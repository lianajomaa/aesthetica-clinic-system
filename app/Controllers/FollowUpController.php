<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

final class FollowUpController extends Controller
{
    public function index(): void
    {
        $filter = $this->query('filter', 'pending');

        $where = match ($filter) {
            'overdue' => "f.status = 'pending' AND f.due_date < CURDATE()",
            'recall'  => "f.status = 'pending' AND f.type = 'recall'",
            'done'    => "f.status = 'done'",
            default   => "f.status = 'pending'",
        };

        $this->view('followups/index', [
            'title'  => 'Follow-ups & recalls',
            'filter' => $filter,
            'items'  => DB::all(
                "SELECT f.*, p.full_name AS patient_name, p.code, p.phone,
                        t.name AS treatment_name
                   FROM follow_ups f
                   JOIN patients p ON p.id = f.patient_id
                   LEFT JOIN treatment_records r ON r.id = f.treatment_record_id
                   LEFT JOIN treatments t ON t.id = r.treatment_id
                  WHERE $where
                  ORDER BY f.due_date ASC LIMIT 200"
            ),
            'counts' => [
                'pending' => (int) DB::value("SELECT COUNT(*) FROM follow_ups WHERE status='pending'"),
                'overdue' => (int) DB::value("SELECT COUNT(*) FROM follow_ups WHERE status='pending' AND due_date < CURDATE()"),
                'recall'  => (int) DB::value("SELECT COUNT(*) FROM follow_ups WHERE status='pending' AND type='recall'"),
            ],
        ]);
    }

    public function complete(int $id): void
    {
        Csrf::verify();
        DB::update('follow_ups', $id, [
            'status' => $this->input('action') === 'dismiss' ? 'dismissed' : 'done',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);
        Audit::log('complete', 'follow_up', $id);
        Flash::add('success', 'Follow-up closed.');
        $this->redirect('/followups?filter=' . ($this->input('filter') ?: 'pending'));
    }
}
