<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

/**
 * Treatment records — what was injected, where, from which batch.
 * Recording a treatment also deducts stock and raises the follow-ups.
 */
final class RecordController extends Controller
{
    public function index(): void
    {
        $records = DB::all(
            'SELECT r.*, p.full_name AS patient_name, p.code AS patient_code,
                    t.name AS treatment_name, t.category, u.full_name AS practitioner_name
               FROM treatment_records r
               JOIN patients p ON p.id = r.patient_id
               JOIN treatments t ON t.id = r.treatment_id
               JOIN practitioners pr ON pr.id = r.practitioner_id
               JOIN users u ON u.id = pr.user_id
              ORDER BY r.performed_at DESC LIMIT 100'
        );
        $this->view('records/index', ['title' => 'Treatment records', 'records' => $records]);
    }

    public function create(): void
    {
        $apptId = (int) ($this->query('appointment') ?? 0);
        $appt = $apptId ? DB::one(
            'SELECT a.*, p.full_name AS patient_name, t.name AS treatment_name, t.price_type
               FROM appointments a
               JOIN patients p ON p.id = a.patient_id
               JOIN treatments t ON t.id = a.treatment_id
              WHERE a.id = ?', [$apptId]
        ) : null;

        $this->view('records/form', [
            'title'         => 'Record a treatment',
            'appt'          => $appt,
            'patients'      => DB::all('SELECT id, code, full_name FROM patients ORDER BY full_name'),
            'treatments'    => DB::all("SELECT * FROM treatments WHERE is_active = 1 AND category <> 'consultation' ORDER BY name"),
            'practitioners' => DB::all(
                'SELECT pr.id, u.full_name FROM practitioners pr
                   JOIN users u ON u.id = pr.user_id WHERE pr.is_active = 1 ORDER BY u.full_name'
            ),
            'batches' => DB::all(
                'SELECT b.id, b.lot_number, b.expiry_date, b.qty_remaining,
                        p.id AS product_id, p.name, p.brand, p.unit
                   FROM batches b JOIN products p ON p.id = b.product_id
                  WHERE b.qty_remaining > 0 AND b.expiry_date >= CURDATE()
                  ORDER BY p.name, b.expiry_date'
            ),
        ]);
    }

    public function store(): void
    {
        Csrf::verify();

        $patientId = $this->intInput('patient_id');
        $treatId   = $this->intInput('treatment_id');
        $pracId    = $this->intInput('practitioner_id') ?: Auth::practitionerId();
        $apptId    = $this->intInput('appointment_id');

        if (!$patientId || !$treatId || !$pracId) {
            Flash::add('error', 'Patient, treatment and practitioner are all required.');
            $this->redirect('/records/create');
        }

        $treatment = DB::one('SELECT * FROM treatments WHERE id = ?', [$treatId]);

        // A treatment that needs consent cannot be recorded without one.
        if ((int) $treatment['requires_consent'] === 1) {
            $consent = DB::one(
                'SELECT id FROM consents
                  WHERE patient_id = ? AND treatment_id = ? AND signed_at IS NOT NULL
                    AND signed_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                  ORDER BY id DESC', [$patientId, $treatId]
            );
            if (!$consent) {
                Flash::add('error', 'A signed consent form is required before this treatment can be recorded.');
                $this->redirect('/consents/create?patient=' . $patientId . '&treatment=' . $treatId
                    . ($apptId ? '&appointment=' . $apptId : ''));
            }
        }

        $sites     = $_POST['site'] ?? [];
        $batchIds  = $_POST['batch_id'] ?? [];
        $quantities= $_POST['quantity'] ?? [];

        $recordId = DB::transaction(function () use (
            $patientId, $treatId, $pracId, $apptId, $sites, $batchIds, $quantities, $treatment
        ) {
            $recordId = DB::insert('treatment_records', [
                'appointment_id'  => $apptId ?: null,
                'patient_id'      => $patientId,
                'practitioner_id' => $pracId,
                'treatment_id'    => $treatId,
                'performed_at'    => $this->input('performed_at') ?: date('Y-m-d H:i:s'),
                'technique'       => $this->input('technique') ?: null,
                'notes'           => $this->input('notes') ?: null,
            ]);

            $totalUnits = 0.0;
            $totalMl    = 0.0;

            foreach ($sites as $i => $site) {
                $site = trim((string) $site);
                $batchId = (int) ($batchIds[$i] ?? 0);
                $qty = (float) ($quantities[$i] ?? 0);
                if ($site === '' || $batchId === 0 || $qty <= 0) {
                    continue;
                }

                $batch = DB::one(
                    'SELECT b.*, p.unit, p.id AS product_id FROM batches b
                       JOIN products p ON p.id = b.product_id WHERE b.id = ?', [$batchId]
                );
                if (!$batch) {
                    continue;
                }

                DB::insert('treatment_record_items', [
                    'treatment_record_id' => $recordId,
                    'product_id'          => (int) $batch['product_id'],
                    'batch_id'            => $batchId,
                    'injection_site'      => $site,
                    'quantity'            => $qty,
                    'unit'                => $batch['unit'],
                ]);

                // deduct from that exact batch
                DB::run('UPDATE batches SET qty_remaining = GREATEST(qty_remaining - ?, 0) WHERE id = ?',
                    [$qty, $batchId]);
                DB::insert('stock_movements', [
                    'batch_id' => $batchId, 'type' => 'out', 'quantity' => $qty,
                    'reference_type' => 'treatment_record', 'reference_id' => $recordId,
                    'note' => 'Used in treatment', 'user_id' => Auth::id(),
                ]);

                if ($batch['unit'] === 'unit') {
                    $totalUnits += $qty;
                } elseif ($batch['unit'] === 'ml') {
                    $totalMl += $qty;
                }
            }

            DB::update('treatment_records', $recordId, [
                'total_units' => $totalUnits, 'total_ml' => $totalMl,
            ]);

            // follow-up and recall come from the treatment definition
            $performed = $this->input('performed_at') ?: date('Y-m-d H:i:s');
            if (!empty($treatment['followup_days'])) {
                DB::insert('follow_ups', [
                    'patient_id' => $patientId, 'treatment_record_id' => $recordId, 'type' => 'review',
                    'due_date' => date('Y-m-d', strtotime($performed) + (int) $treatment['followup_days'] * 86400),
                    'notes' => 'Review after ' . $treatment['followup_days'] . ' days',
                ]);
            }
            if (!empty($treatment['recall_days'])) {
                DB::insert('follow_ups', [
                    'patient_id' => $patientId, 'treatment_record_id' => $recordId, 'type' => 'recall',
                    'due_date' => date('Y-m-d', strtotime($performed) + (int) $treatment['recall_days'] * 86400),
                    'notes' => 'Due for repeat treatment',
                ]);
            }

            if ($apptId) {
                DB::update('appointments', $apptId, ['status' => 'completed']);
            }

            return $recordId;
        });

        Audit::log('create', 'treatment_record', $recordId);
        Flash::add('success', 'Treatment recorded, stock deducted and follow-up scheduled.');
        $this->redirect("/records/$recordId");
    }

    public function show(int $id): void
    {
        $record = DB::one(
            'SELECT r.*, p.full_name AS patient_name, p.code AS patient_code, p.id AS patient_id,
                    t.name AS treatment_name, t.category, u.full_name AS practitioner_name,
                    ac.title AS aftercare_title, ac.body AS aftercare_body
               FROM treatment_records r
               JOIN patients p ON p.id = r.patient_id
               JOIN treatments t ON t.id = r.treatment_id
               LEFT JOIN aftercare_templates ac ON ac.id = t.aftercare_id
               JOIN practitioners pr ON pr.id = r.practitioner_id
               JOIN users u ON u.id = pr.user_id
              WHERE r.id = ?', [$id]
        );
        if (!$record) {
            $this->notFound('Record');
        }

        $this->view('records/show', [
            'title'  => 'Treatment record',
            'record' => $record,
            'items'  => DB::all(
                'SELECT i.*, p.name, p.brand, b.lot_number, b.expiry_date
                   FROM treatment_record_items i
                   JOIN products p ON p.id = i.product_id
                   JOIN batches b ON b.id = i.batch_id
                  WHERE i.treatment_record_id = ?', [$id]
            ),
            'followups' => DB::all('SELECT * FROM follow_ups WHERE treatment_record_id = ?', [$id]),
        ]);
    }
}
