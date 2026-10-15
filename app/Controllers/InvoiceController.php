<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

final class InvoiceController extends Controller
{
    public function index(): void
    {
        $status = $this->query('status', '');
        $where = '';
        $params = [];
        if (in_array($status, ['unpaid','partial','paid','void'], true)) {
            $where = 'WHERE i.status = ?';
            $params[] = $status;
        }

        $this->view('invoices/index', [
            'title'  => 'Invoices',
            'status' => $status,
            'invoices' => DB::all(
                "SELECT i.*, p.full_name AS patient_name, p.code
                   FROM invoices i JOIN patients p ON p.id = i.patient_id
                   $where
                  ORDER BY i.issued_at DESC LIMIT 150", $params
            ),
            'totals' => [
                'outstanding' => (float) DB::value(
                    "SELECT COALESCE(SUM(total - amount_paid),0) FROM invoices WHERE status IN ('unpaid','partial')"
                ),
                'month' => (float) DB::value(
                    'SELECT COALESCE(SUM(amount),0) FROM payments WHERE paid_at >= ?', [date('Y-m-01 00:00:00')]
                ),
            ],
        ]);
    }

    public function create(): void
    {
        $apptId = (int) ($this->query('appointment') ?? 0);
        $appt = $apptId ? DB::one(
            'SELECT a.*, p.full_name AS patient_name, t.name AS treatment_name,
                    t.price, t.price_type,
                    (SELECT total_units FROM treatment_records r WHERE r.appointment_id = a.id) AS units
               FROM appointments a
               JOIN patients p ON p.id = a.patient_id
               JOIN treatments t ON t.id = a.treatment_id
              WHERE a.id = ?', [$apptId]
        ) : null;

        $this->view('invoices/form', [
            'title'    => 'New invoice',
            'appt'     => $appt,
            'patients' => DB::all('SELECT id, code, full_name FROM patients ORDER BY full_name'),
        ]);
    }

    public function store(): void
    {
        Csrf::verify();
        $patientId = $this->intInput('patient_id');
        if (!$patientId) {
            Flash::add('error', 'Choose a patient.');
            $this->redirect('/invoices/create');
        }

        $descriptions = $_POST['description'] ?? [];
        $quantities   = $_POST['quantity'] ?? [];
        $prices       = $_POST['unit_price'] ?? [];
        $discount     = $this->floatInput('discount');

        $id = DB::transaction(function () use ($patientId, $descriptions, $quantities, $prices, $discount) {
            $number = 'INV-' . (1000 + (int) DB::value('SELECT COALESCE(MAX(id),0) + 1 FROM invoices'));
            $invoiceId = DB::insert('invoices', [
                'number'         => $number,
                'patient_id'     => $patientId,
                'appointment_id' => $this->intInput('appointment_id') ?: null,
                'subtotal' => 0, 'discount' => $discount, 'total' => 0,
                'created_by' => Auth::id(),
            ]);

            $subtotal = 0.0;
            foreach ($descriptions as $i => $desc) {
                $desc = trim((string) $desc);
                if ($desc === '') {
                    continue;
                }
                $qty   = (float) ($quantities[$i] ?? 1);
                $price = (float) ($prices[$i] ?? 0);
                $line  = round($qty * $price, 2);
                $subtotal += $line;
                DB::insert('invoice_items', [
                    'invoice_id' => $invoiceId, 'description' => $desc,
                    'quantity' => $qty, 'unit_price' => $price, 'line_total' => $line,
                ]);
            }

            $total = max(0, round($subtotal - $discount, 2));
            DB::update('invoices', $invoiceId, ['subtotal' => $subtotal, 'total' => $total]);
            return $invoiceId;
        });

        Audit::log('create', 'invoice', $id);
        Flash::add('success', 'Invoice created.');
        $this->redirect("/invoices/$id");
    }

    public function show(int $id): void
    {
        $invoice = DB::one(
            'SELECT i.*, p.full_name AS patient_name, p.code, p.phone, p.email
               FROM invoices i JOIN patients p ON p.id = i.patient_id WHERE i.id = ?', [$id]
        );
        if (!$invoice) {
            $this->notFound('Invoice');
        }

        $this->view('invoices/show', [
            'title'    => $invoice['number'],
            'invoice'  => $invoice,
            'items'    => DB::all('SELECT * FROM invoice_items WHERE invoice_id = ?', [$id]),
            'payments' => DB::all(
                'SELECT pay.*, u.full_name AS user_name FROM payments pay
                   LEFT JOIN users u ON u.id = pay.received_by
                  WHERE pay.invoice_id = ? ORDER BY pay.paid_at', [$id]
            ),
            'settings' => $this->settings(),
        ]);
    }

    public function pay(int $id): void
    {
        Csrf::verify();
        $invoice = DB::one('SELECT * FROM invoices WHERE id = ?', [$id]);
        if (!$invoice) {
            $this->notFound('Invoice');
        }

        $amount = $this->floatInput('amount');
        if ($amount <= 0) {
            Flash::add('error', 'Enter an amount.');
            $this->redirect("/invoices/$id");
        }

        DB::transaction(function () use ($id, $invoice, $amount) {
            DB::insert('payments', [
                'invoice_id' => $id,
                'method'     => $this->input('method', 'cash'),
                'amount'     => $amount,
                'reference'  => $this->input('reference') ?: null,
                'received_by'=> Auth::id(),
            ]);
            $paid = (float) $invoice['amount_paid'] + $amount;
            DB::update('invoices', $id, [
                'amount_paid' => $paid,
                'status'      => $paid >= (float) $invoice['total'] ? 'paid' : 'partial',
            ]);
        });

        Audit::log('payment', 'invoice', $id, (string) $amount);
        Flash::add('success', 'Payment recorded.');
        $this->redirect("/invoices/$id");
    }

    private function settings(): array
    {
        $rows = DB::all('SELECT `key`, `value` FROM settings');
        return array_column($rows, 'value', 'key');
    }
}
