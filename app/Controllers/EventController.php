<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

/** Adverse events — complications recorded against a patient and a treatment. */
final class EventController extends Controller
{
    public function index(): void
    {
        $this->view('events/index', [
            'title' => 'Adverse events',
            'events' => DB::all(
                'SELECT e.*, p.full_name AS patient_name, p.code, u.full_name AS reporter,
                        t.name AS treatment_name, b.lot_number
                   FROM adverse_events e
                   JOIN patients p ON p.id = e.patient_id
                   LEFT JOIN users u ON u.id = e.reported_by
                   LEFT JOIN treatment_records r ON r.id = e.treatment_record_id
                   LEFT JOIN treatments t ON t.id = r.treatment_id
                   LEFT JOIN treatment_record_items i ON i.treatment_record_id = r.id
                   LEFT JOIN batches b ON b.id = i.batch_id
                  GROUP BY e.id
                  ORDER BY e.reported_at DESC'
            ),
        ]);
    }

    public function create(): void
    {
        $this->view('events/form', [
            'title'    => 'Record an adverse event',
            'patients' => DB::all('SELECT id, code, full_name FROM patients ORDER BY full_name'),
            'records'  => DB::all(
                'SELECT r.id, r.performed_at, r.patient_id, t.name AS treatment_name
                   FROM treatment_records r JOIN treatments t ON t.id = r.treatment_id
                  ORDER BY r.performed_at DESC LIMIT 200'
            ),
            'prefill'  => ['patient_id' => (int) ($this->query('patient') ?? 0)],
        ]);
    }

    public function store(): void
    {
        Csrf::verify();
        $v = $this->validate(
            ['patient_id' => 'required|numeric', 'type' => 'required|max:100'],
            ['type' => 'Event type']
        );
        if ($v->fails()) {
            Flash::add('error', $v->firstError());
            $this->redirect('/events/create');
        }

        $id = DB::insert('adverse_events', [
            'patient_id'          => $this->intInput('patient_id'),
            'treatment_record_id' => $this->intInput('treatment_record_id') ?: null,
            'type'                => $this->input('type'),
            'severity'            => $this->input('severity', 'mild'),
            'description'         => $this->input('description') ?: null,
            'action_taken'        => $this->input('action_taken') ?: null,
            'resolved'            => isset($_POST['resolved']) ? 1 : 0,
            'reported_by'         => Auth::id(),
        ]);

        Audit::log('create', 'adverse_event', $id, $this->input('type'));
        Flash::add('success', 'Adverse event recorded.');
        $this->redirect('/events');
    }
}
