<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

final class AppointmentController extends Controller
{
    private const STATUSES = ['booked','confirmed','arrived','in_room','completed','no_show','cancelled'];

    public function index(): void
    {
        $date = $this->query('date') ?: date('Y-m-d');
        $prac = (int) ($this->query('practitioner') ?? 0);

        $sql = "SELECT a.*, p.full_name AS patient_name, p.code AS patient_code, p.phone,
                       t.name AS treatment_name, t.category, t.duration_min,
                       u.full_name AS practitioner_name, pr.calendar_colour
                  FROM appointments a
                  JOIN patients p ON p.id = a.patient_id
                  JOIN treatments t ON t.id = a.treatment_id
                  JOIN practitioners pr ON pr.id = a.practitioner_id
                  JOIN users u ON u.id = pr.user_id
                 WHERE DATE(a.starts_at) = ?";
        $params = [$date];
        if ($prac > 0) {
            $sql .= ' AND a.practitioner_id = ?';
            $params[] = $prac;
        }
        $sql .= ' ORDER BY a.starts_at';

        $this->view('appointments/index', [
            'title'         => 'Diary',
            'date'          => $date,
            'appointments'  => DB::all($sql, $params),
            'practitioners' => $this->practitioners(),
            'selectedPrac'  => $prac,
        ]);
    }

    public function create(): void
    {
        $this->view('appointments/form', [
            'title'         => 'New appointment',
            'patients'      => DB::all('SELECT id, code, full_name, phone FROM patients ORDER BY full_name'),
            'treatments'    => DB::all('SELECT * FROM treatments WHERE is_active = 1 ORDER BY category, name'),
            'practitioners' => $this->practitioners(),
            'prefill'       => [
                'date'       => $this->query('date') ?: date('Y-m-d'),
                'patient_id' => (int) ($this->query('patient') ?? 0),
            ],
        ]);
    }

    public function store(): void
    {
        Csrf::verify();

        $v = $this->validate([
            'patient_id'      => 'required|numeric',
            'practitioner_id' => 'required|numeric',
            'treatment_id'    => 'required|numeric',
            'date'            => 'required|date',
            'time'            => 'required',
        ], ['practitioner_id' => 'Practitioner', 'treatment_id' => 'Treatment']);

        if ($v->fails()) {
            Flash::add('error', $v->firstError());
            $this->redirect('/appointments/create');
        }

        $treatment = DB::one('SELECT * FROM treatments WHERE id = ?', [$this->intInput('treatment_id')]);
        if (!$treatment) {
            Flash::add('error', 'That treatment no longer exists.');
            $this->redirect('/appointments/create');
        }

        $start = $this->input('date') . ' ' . $this->input('time') . ':00';
        $end   = date('Y-m-d H:i:s', strtotime($start) + (int) $treatment['duration_min'] * 60);
        $prac  = $this->intInput('practitioner_id');

        if ($msg = $this->unavailable($prac, $start, $end)) {
            Flash::add('error', $msg);
            $this->redirect('/appointments/create');
        }

        $id = DB::insert('appointments', [
            'patient_id'      => $this->intInput('patient_id'),
            'practitioner_id' => $prac,
            'treatment_id'    => (int) $treatment['id'],
            'starts_at'       => $start,
            'ends_at'         => $end,
            'status'          => 'booked',
            'deposit'         => $this->floatInput('deposit'),
            'notes'           => $this->input('notes') ?: null,
            'created_by'      => Auth::id(),
        ]);

        Audit::log('create', 'appointment', $id);
        Flash::add('success', 'Appointment booked for ' . date('D j M, H:i', strtotime($start)) . '.');
        $this->redirect('/appointments?date=' . $this->input('date'));
    }

    /**
     * Returns a reason the slot cannot be used, or null when it is free.
     * This is the rule that stops two patients being booked onto one injector.
     */
    private function unavailable(int $practitionerId, string $start, string $end, ?int $ignoreId = null): ?string
    {
        $weekday = (int) date('w', strtotime($start));
        $schedule = DB::all(
            'SELECT * FROM practitioner_schedules WHERE practitioner_id = ? AND weekday = ?',
            [$practitionerId, $weekday]
        );
        if (!$schedule) {
            return 'That practitioner does not work on ' . date('l', strtotime($start)) . '.';
        }

        $startTime = date('H:i:s', strtotime($start));
        $endTime   = date('H:i:s', strtotime($end));
        $inHours = false;
        foreach ($schedule as $s) {
            if ($startTime >= $s['start_time'] && $endTime <= $s['end_time']) {
                $inHours = true;
                break;
            }
        }
        if (!$inHours) {
            $w = $schedule[0];
            return 'Outside working hours (' . substr($w['start_time'], 0, 5)
                 . '–' . substr($w['end_time'], 0, 5) . ').';
        }

        $off = DB::one(
            'SELECT reason FROM time_off WHERE practitioner_id = ? AND starts_at < ? AND ends_at > ?',
            [$practitionerId, $end, $start]
        );
        if ($off) {
            return 'That practitioner is away' . ($off['reason'] ? ' — ' . $off['reason'] : '') . '.';
        }

        $sql = "SELECT a.starts_at, p.full_name
                  FROM appointments a JOIN patients p ON p.id = a.patient_id
                 WHERE a.practitioner_id = ?
                   AND a.status NOT IN ('cancelled','no_show')
                   AND a.starts_at < ? AND a.ends_at > ?";
        $params = [$practitionerId, $end, $start];
        if ($ignoreId !== null) {
            $sql .= ' AND a.id <> ?';
            $params[] = $ignoreId;
        }
        if ($clash = DB::one($sql, $params)) {
            return 'That slot overlaps an existing appointment at '
                 . date('H:i', strtotime($clash['starts_at'])) . '.';
        }

        return null;
    }

    public function show(int $id): void
    {
        $appt = DB::one(
            'SELECT a.*, p.full_name AS patient_name, p.code AS patient_code, p.phone, p.id AS patient_id,
                    t.name AS treatment_name, t.category, t.price, t.price_type, t.requires_consent,
                    t.duration_min, u.full_name AS practitioner_name
               FROM appointments a
               JOIN patients p ON p.id = a.patient_id
               JOIN treatments t ON t.id = a.treatment_id
               JOIN practitioners pr ON pr.id = a.practitioner_id
               JOIN users u ON u.id = pr.user_id
              WHERE a.id = ?', [$id]
        );
        if (!$appt) {
            $this->notFound('Appointment');
        }

        $this->view('appointments/show', [
            'title'    => 'Appointment',
            'appt'     => $appt,
            'consent'  => DB::one('SELECT * FROM consents WHERE appointment_id = ? ORDER BY id DESC', [$id]),
            'record'   => DB::one('SELECT * FROM treatment_records WHERE appointment_id = ?', [$id]),
            'invoice'  => DB::one('SELECT * FROM invoices WHERE appointment_id = ?', [$id]),
            'statuses' => self::STATUSES,
        ]);
    }

    public function status(int $id): void
    {
        Csrf::verify();
        $status = $this->input('status', 'booked');
        if (!in_array($status, self::STATUSES, true)) {
            Flash::add('error', 'Unknown status.');
            $this->redirect("/appointments/$id");
        }
        DB::update('appointments', $id, ['status' => $status]);
        Audit::log('status', 'appointment', $id, $status);
        Flash::add('success', 'Appointment marked as ' . str_replace('_', ' ', $status) . '.');
        $this->redirect("/appointments/$id");
    }

    public function destroy(int $id): void
    {
        Csrf::verify();
        DB::update('appointments', $id, ['status' => 'cancelled']);
        Audit::log('cancel', 'appointment', $id);
        Flash::add('success', 'Appointment cancelled.');
        $this->redirect('/appointments');
    }

    private function practitioners(): array
    {
        return DB::all(
            'SELECT pr.id, pr.calendar_colour, pr.speciality, u.full_name
               FROM practitioners pr JOIN users u ON u.id = pr.user_id
              WHERE pr.is_active = 1 ORDER BY u.full_name'
        );
    }
}
