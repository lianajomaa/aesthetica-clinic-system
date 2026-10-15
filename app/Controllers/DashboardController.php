<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database as DB;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $today     = date('Y-m-d');
        $weekStart = date('Y-m-d', strtotime('monday this week'));
        $monthStart= date('Y-m-01');
        $isClinician = Auth::practitionerId() !== null;
        $mine      = Auth::practitionerId();

        // --- today's list (filtered to the signed-in practitioner when relevant)
        $params = [$today];
        $onlyMine = '';
        if ($isClinician && Auth::role() === 'doctor') {
            $onlyMine = ' AND a.practitioner_id = ?';
            $params[] = $mine;
        }
        $todayList = DB::all(
            "SELECT a.*, p.full_name AS patient_name, p.code AS patient_code,
                    t.name AS treatment_name, t.category, u.full_name AS practitioner_name,
                    pr.calendar_colour,
                    (SELECT COUNT(*) FROM consents c
                      WHERE c.appointment_id = a.id AND c.signed_at IS NOT NULL) AS consent_signed,
                    t.requires_consent
               FROM appointments a
               JOIN patients p      ON p.id = a.patient_id
               JOIN treatments t    ON t.id = a.treatment_id
               JOIN practitioners pr ON pr.id = a.practitioner_id
               JOIN users u         ON u.id = pr.user_id
              WHERE DATE(a.starts_at) = ? $onlyMine
              ORDER BY a.starts_at",
            $params
        );

        // --- headline numbers
        $stats = [
            'today_total'    => count($todayList),
            'today_consults' => count(array_filter($todayList, fn ($a) => $a['category'] === 'consultation')),
            'week_revenue'   => (float) DB::value(
                'SELECT COALESCE(SUM(amount),0) FROM payments WHERE paid_at >= ?', [$weekStart . ' 00:00:00']
            ),
            'last_week_revenue' => (float) DB::value(
                'SELECT COALESCE(SUM(amount),0) FROM payments WHERE paid_at >= ? AND paid_at < ?',
                [date('Y-m-d', strtotime($weekStart . ' -7 days')) . ' 00:00:00', $weekStart . ' 00:00:00']
            ),
            'month_revenue'  => (float) DB::value(
                'SELECT COALESCE(SUM(amount),0) FROM payments WHERE paid_at >= ?', [$monthStart . ' 00:00:00']
            ),
            'units_month'    => (float) DB::value(
                "SELECT COALESCE(SUM(i.quantity),0)
                   FROM treatment_record_items i
                   JOIN treatment_records r ON r.id = i.treatment_record_id
                  WHERE i.unit = 'unit' AND r.performed_at >= ?", [$monthStart . ' 00:00:00']
            ),
            'followups_due'  => (int) DB::value(
                "SELECT COUNT(*) FROM follow_ups WHERE status='pending' AND due_date <= ?",
                [date('Y-m-d', strtotime('+7 days'))]
            ),
            'followups_over' => (int) DB::value(
                "SELECT COUNT(*) FROM follow_ups WHERE status='pending' AND due_date < ?", [$today]
            ),
            'new_patients'   => (int) DB::value(
                'SELECT COUNT(*) FROM patients WHERE created_at >= ?', [$monthStart . ' 00:00:00']
            ),
        ];
        $stats['revenue_change'] = $stats['last_week_revenue'] > 0
            ? round((($stats['week_revenue'] - $stats['last_week_revenue']) / $stats['last_week_revenue']) * 100)
            : null;

        // --- alerts: the panel that makes the dashboard useful
        $alerts = [
            'low_stock' => DB::all(
                'SELECT p.id, p.name, p.brand, p.unit, p.reorder_level,
                        COALESCE(SUM(b.qty_remaining),0) AS remaining
                   FROM products p
                   LEFT JOIN batches b ON b.product_id = p.id AND b.expiry_date >= CURDATE()
                  WHERE p.is_active = 1
                  GROUP BY p.id
                 HAVING remaining <= p.reorder_level
                  ORDER BY remaining ASC'
            ),
            'expiring' => DB::all(
                'SELECT b.*, p.name, p.brand, p.unit,
                        DATEDIFF(b.expiry_date, CURDATE()) AS days_left
                   FROM batches b JOIN products p ON p.id = b.product_id
                  WHERE b.qty_remaining > 0 AND b.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 45 DAY)
                  ORDER BY b.expiry_date ASC'
            ),
            'unsigned_consents' => array_values(array_filter(
                $todayList,
                fn ($a) => (int) $a['requires_consent'] === 1
                        && (int) $a['consent_signed'] === 0
                        && !in_array($a['status'], ['cancelled', 'no_show'], true)
            )),
            'overdue_followups' => DB::all(
                "SELECT f.*, p.full_name AS patient_name, p.phone, p.code
                   FROM follow_ups f JOIN patients p ON p.id = f.patient_id
                  WHERE f.status = 'pending' AND f.due_date < CURDATE()
                  ORDER BY f.due_date ASC LIMIT 8"
            ),
            'recalls_due' => DB::all(
                "SELECT f.*, p.full_name AS patient_name, p.phone, p.code
                   FROM follow_ups f JOIN patients p ON p.id = f.patient_id
                  WHERE f.status = 'pending' AND f.type = 'recall'
                    AND f.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 21 DAY)
                  ORDER BY f.due_date ASC LIMIT 8"
            ),
        ];

        // --- performance
        $byPractitioner = DB::all(
            "SELECT u.full_name, pr.calendar_colour, pr.commission_rate,
                    COUNT(DISTINCT r.id) AS treatments,
                    COALESCE(SUM(pay.amount),0) AS revenue
               FROM practitioners pr
               JOIN users u ON u.id = pr.user_id
               LEFT JOIN treatment_records r ON r.practitioner_id = pr.id AND r.performed_at >= ?
               LEFT JOIN appointments a ON a.id = r.appointment_id
               LEFT JOIN invoices inv ON inv.appointment_id = a.id
               LEFT JOIN payments pay ON pay.invoice_id = inv.id
              GROUP BY pr.id
              ORDER BY revenue DESC",
            [$monthStart . ' 00:00:00']
        );

        $topTreatments = DB::all(
            "SELECT t.name, t.category, COUNT(r.id) AS performed,
                    COALESCE(SUM(ii.line_total),0) AS revenue
               FROM treatment_records r
               JOIN treatments t ON t.id = r.treatment_id
               LEFT JOIN invoices inv ON inv.appointment_id = r.appointment_id
               LEFT JOIN invoice_items ii ON ii.invoice_id = inv.id
              WHERE r.performed_at >= ?
              GROUP BY t.id ORDER BY revenue DESC LIMIT 6",
            [date('Y-m-d', strtotime('-90 days')) . ' 00:00:00']
        );

        $this->view('dashboard/index', [
            'title'          => 'Dashboard',
            'todayList'      => $todayList,
            'stats'          => $stats,
            'alerts'         => $alerts,
            'byPractitioner' => $byPractitioner,
            'topTreatments'  => $topTreatments,
        ]);
    }
}
