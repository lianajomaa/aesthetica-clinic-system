<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database as DB;

final class PractitionerController extends Controller
{
    public function index(): void
    {
        $this->view('practitioners/index', [
            'title' => 'Practitioners',
            'practitioners' => DB::all(
                "SELECT pr.*, u.full_name, u.email, u.phone, u.avatar_initials,
                        (SELECT COUNT(*) FROM appointments a
                          WHERE a.practitioner_id = pr.id AND DATE(a.starts_at) = CURDATE()
                            AND a.status NOT IN ('cancelled','no_show')) AS today_count,
                        (SELECT COUNT(*) FROM treatment_records r
                          WHERE r.practitioner_id = pr.id
                            AND r.performed_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01')) AS month_treatments
                   FROM practitioners pr
                   JOIN users u ON u.id = pr.user_id
                  ORDER BY u.full_name"
            ),
        ]);
    }

    public function show(int $id): void
    {
        $prac = DB::one(
            'SELECT pr.*, u.full_name, u.email, u.phone, u.avatar_initials
               FROM practitioners pr JOIN users u ON u.id = pr.user_id WHERE pr.id = ?', [$id]
        );
        if (!$prac) {
            $this->notFound('Practitioner');
        }

        $days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        $this->view('practitioners/show', [
            'title'    => $prac['full_name'],
            'prac'     => $prac,
            'days'     => $days,
            'schedule' => DB::all(
                'SELECT * FROM practitioner_schedules WHERE practitioner_id = ? ORDER BY weekday', [$id]
            ),
            'timeOff'  => DB::all(
                'SELECT * FROM time_off WHERE practitioner_id = ? AND ends_at >= NOW() ORDER BY starts_at', [$id]
            ),
            'today'    => DB::all(
                'SELECT a.*, p.full_name AS patient_name, t.name AS treatment_name
                   FROM appointments a
                   JOIN patients p ON p.id = a.patient_id
                   JOIN treatments t ON t.id = a.treatment_id
                  WHERE a.practitioner_id = ? AND DATE(a.starts_at) = CURDATE()
                  ORDER BY a.starts_at', [$id]
            ),
            'stats'    => [
                'treatments' => (int) DB::value(
                    "SELECT COUNT(*) FROM treatment_records WHERE practitioner_id = ?
                      AND performed_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01')", [$id]
                ),
                'revenue' => (float) DB::value(
                    "SELECT COALESCE(SUM(pay.amount),0)
                       FROM treatment_records r
                       JOIN invoices i ON i.appointment_id = r.appointment_id
                       JOIN payments pay ON pay.invoice_id = i.id
                      WHERE r.practitioner_id = ?
                        AND r.performed_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01')", [$id]
                ),
                'units' => (float) DB::value(
                    "SELECT COALESCE(SUM(total_units),0) FROM treatment_records
                      WHERE practitioner_id = ? AND performed_at >= DATE_FORMAT(CURDATE(),'%Y-%m-01')", [$id]
                ),
            ],
        ]);
    }
}
