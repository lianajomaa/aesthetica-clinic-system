<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database as DB;

final class ReportController extends Controller
{
    public function index(): void
    {
        $from = $this->query('from') ?: date('Y-m-d', strtotime('-90 days'));
        $to   = $this->query('to')   ?: date('Y-m-d');
        $range = [$from . ' 00:00:00', $to . ' 23:59:59'];

        $this->view('reports/index', [
            'title' => 'Reports',
            'from'  => $from,
            'to'    => $to,

            'summary' => [
                'revenue' => (float) DB::value(
                    'SELECT COALESCE(SUM(amount),0) FROM payments WHERE paid_at BETWEEN ? AND ?', $range),
                'treatments' => (int) DB::value(
                    'SELECT COUNT(*) FROM treatment_records WHERE performed_at BETWEEN ? AND ?', $range),
                'new_patients' => (int) DB::value(
                    'SELECT COUNT(*) FROM patients WHERE created_at BETWEEN ? AND ?', $range),
                'no_shows' => (int) DB::value(
                    "SELECT COUNT(*) FROM appointments WHERE status='no_show' AND starts_at BETWEEN ? AND ?", $range),
                'appointments' => (int) DB::value(
                    'SELECT COUNT(*) FROM appointments WHERE starts_at BETWEEN ? AND ?', $range),
                'product_cost' => (float) DB::value(
                    'SELECT COALESCE(SUM(i.quantity * p.unit_cost),0)
                       FROM treatment_record_items i
                       JOIN products p ON p.id = i.product_id
                       JOIN treatment_records r ON r.id = i.treatment_record_id
                      WHERE r.performed_at BETWEEN ? AND ?', $range),
            ],

            'monthly' => DB::all(
                "SELECT DATE_FORMAT(paid_at, '%Y-%m') AS month, SUM(amount) AS revenue, COUNT(*) AS payments
                   FROM payments WHERE paid_at BETWEEN ? AND ?
                  GROUP BY month ORDER BY month", $range),

            'byTreatment' => DB::all(
                'SELECT t.name, t.category, COUNT(r.id) AS performed,
                        COALESCE(SUM(ii.line_total),0) AS revenue,
                        COALESCE(SUM(r.total_units),0) AS units
                   FROM treatment_records r
                   JOIN treatments t ON t.id = r.treatment_id
                   LEFT JOIN invoices inv ON inv.appointment_id = r.appointment_id
                   LEFT JOIN invoice_items ii ON ii.invoice_id = inv.id
                  WHERE r.performed_at BETWEEN ? AND ?
                  GROUP BY t.id ORDER BY revenue DESC', $range),

            'byPractitioner' => DB::all(
                'SELECT u.full_name, pr.commission_rate, pr.calendar_colour,
                        COUNT(r.id) AS treatments,
                        COALESCE(SUM(pay.amount),0) AS revenue
                   FROM practitioners pr
                   JOIN users u ON u.id = pr.user_id
                   LEFT JOIN treatment_records r ON r.practitioner_id = pr.id
                        AND r.performed_at BETWEEN ? AND ?
                   LEFT JOIN invoices inv ON inv.appointment_id = r.appointment_id
                   LEFT JOIN payments pay ON pay.invoice_id = inv.id
                  GROUP BY pr.id ORDER BY revenue DESC', $range),

            'bySource' => DB::all(
                'SELECT source, COUNT(*) AS total FROM patients
                  WHERE created_at BETWEEN ? AND ? GROUP BY source ORDER BY total DESC', $range),

            'productUse' => DB::all(
                'SELECT p.name, p.brand, p.unit, SUM(i.quantity) AS used,
                        SUM(i.quantity * p.unit_cost) AS cost
                   FROM treatment_record_items i
                   JOIN products p ON p.id = i.product_id
                   JOIN treatment_records r ON r.id = i.treatment_record_id
                  WHERE r.performed_at BETWEEN ? AND ?
                  GROUP BY p.id ORDER BY used DESC', $range),

            'retention' => [
                'returning' => (int) DB::value(
                    'SELECT COUNT(*) FROM (SELECT patient_id FROM treatment_records
                        GROUP BY patient_id HAVING COUNT(*) > 1) x'),
                'once' => (int) DB::value(
                    'SELECT COUNT(*) FROM (SELECT patient_id FROM treatment_records
                        GROUP BY patient_id HAVING COUNT(*) = 1) x'),
            ],
        ]);
    }
}
