<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

final class PatientController extends Controller
{
    public function index(): void
    {
        $q = $this->query('q', '');
        $where = '';
        $params = [];
        if ($q !== '') {
            $where = 'WHERE p.full_name LIKE ? OR p.phone LIKE ? OR p.code LIKE ?';
            $params = ["%$q%", "%$q%", "%$q%"];
        }

        $patients = DB::all(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM treatment_records r WHERE r.patient_id = p.id) AS visits,
                    (SELECT MAX(r.performed_at) FROM treatment_records r WHERE r.patient_id = p.id) AS last_visit
               FROM patients p
               $where
              ORDER BY p.full_name
              LIMIT 200",
            $params
        );

        $this->view('patients/index', [
            'title' => 'Patients', 'patients' => $patients, 'q' => $q,
        ]);
    }

    public function create(): void
    {
        $this->view('patients/form', ['title' => 'New patient', 'patient' => null]);
    }

    public function store(): void
    {
        Csrf::verify();
        $v = $this->validate(
            ['full_name' => 'required|max:140', 'phone' => 'required|max:40', 'email' => 'email'],
            ['full_name' => 'Full name']
        );
        if ($v->fails()) {
            Flash::add('error', $v->firstError());
            $this->redirect('/patients/create');
        }

        $next = (int) DB::value('SELECT COALESCE(MAX(id),0) + 100 FROM patients');
        $id = DB::insert('patients', [
            'code'          => sprintf('PT-%05d', $next),
            'full_name'     => $this->input('full_name'),
            'date_of_birth' => $this->input('date_of_birth') ?: null,
            'gender'        => $this->input('gender') ?: null,
            'phone'         => $this->input('phone'),
            'email'         => $this->input('email') ?: null,
            'address'       => $this->input('address') ?: null,
            'source'        => $this->input('source', 'other'),
            'notes'         => $this->input('notes') ?: null,
            'created_by'    => Auth::id(),
        ]);
        DB::insert('medical_histories', ['patient_id' => $id]);

        Audit::log('create', 'patient', $id, $this->input('full_name'));
        Flash::add('success', 'Patient added.');
        $this->redirect("/patients/$id");
    }

    public function show(int $id): void
    {
        $patient = DB::one('SELECT * FROM patients WHERE id = ?', [$id]);
        if (!$patient) {
            $this->notFound('Patient');
        }

        $clinical = Auth::can('patients.clinical');

        $this->view('patients/show', [
            'title'    => $patient['full_name'],
            'patient'  => $patient,
            'clinical' => $clinical,
            'history'  => $clinical ? DB::one('SELECT * FROM medical_histories WHERE patient_id = ?', [$id]) : null,
            'appointments' => DB::all(
                'SELECT a.*, t.name AS treatment_name, u.full_name AS practitioner_name
                   FROM appointments a
                   JOIN treatments t ON t.id = a.treatment_id
                   JOIN practitioners pr ON pr.id = a.practitioner_id
                   JOIN users u ON u.id = pr.user_id
                  WHERE a.patient_id = ? ORDER BY a.starts_at DESC LIMIT 40', [$id]
            ),
            'records' => $clinical ? DB::all(
                'SELECT r.*, t.name AS treatment_name, u.full_name AS practitioner_name
                   FROM treatment_records r
                   JOIN treatments t ON t.id = r.treatment_id
                   JOIN practitioners pr ON pr.id = r.practitioner_id
                   JOIN users u ON u.id = pr.user_id
                  WHERE r.patient_id = ? ORDER BY r.performed_at DESC', [$id]
            ) : [],
            'consents' => DB::all(
                'SELECT c.*, t.name AS treatment_name FROM consents c
                   JOIN treatments t ON t.id = c.treatment_id
                  WHERE c.patient_id = ? ORDER BY c.id DESC LIMIT 20', [$id]
            ),
            'followups' => DB::all(
                'SELECT * FROM follow_ups WHERE patient_id = ? ORDER BY due_date DESC LIMIT 20', [$id]
            ),
            'events' => $clinical ? DB::all(
                'SELECT * FROM adverse_events WHERE patient_id = ? ORDER BY reported_at DESC', [$id]
            ) : [],
            'photos' => $clinical ? DB::all(
                'SELECT * FROM patient_photos WHERE patient_id = ? ORDER BY taken_at DESC LIMIT 12', [$id]
            ) : [],
            'invoices' => Auth::can('billing.view') ? DB::all(
                'SELECT * FROM invoices WHERE patient_id = ? ORDER BY issued_at DESC LIMIT 20', [$id]
            ) : [],
            'spend' => (float) DB::value(
                'SELECT COALESCE(SUM(amount),0) FROM payments pay
                   JOIN invoices i ON i.id = pay.invoice_id WHERE i.patient_id = ?', [$id]
            ),
        ]);
    }

    public function edit(int $id): void
    {
        $patient = DB::one('SELECT * FROM patients WHERE id = ?', [$id]);
        if (!$patient) {
            $this->notFound('Patient');
        }
        $this->view('patients/form', ['title' => 'Edit patient', 'patient' => $patient]);
    }

    public function update(int $id): void
    {
        Csrf::verify();
        if (!DB::one('SELECT id FROM patients WHERE id = ?', [$id])) {
            $this->notFound('Patient');
        }
        DB::update('patients', $id, [
            'full_name'     => $this->input('full_name'),
            'date_of_birth' => $this->input('date_of_birth') ?: null,
            'gender'        => $this->input('gender') ?: null,
            'phone'         => $this->input('phone'),
            'email'         => $this->input('email') ?: null,
            'address'       => $this->input('address') ?: null,
            'source'        => $this->input('source', 'other'),
            'notes'         => $this->input('notes') ?: null,
        ]);
        Audit::log('update', 'patient', $id);
        Flash::add('success', 'Patient updated.');
        $this->redirect("/patients/$id");
    }

    public function history(int $id): void
    {
        Csrf::verify();
        $exists = DB::one('SELECT id FROM medical_histories WHERE patient_id = ?', [$id]);
        $data = [
            'allergies'          => $this->input('allergies'),
            'medications'        => $this->input('medications'),
            'conditions'         => $this->input('conditions'),
            'is_pregnant'        => isset($_POST['is_pregnant']) ? 1 : 0,
            'is_breastfeeding'   => isset($_POST['is_breastfeeding']) ? 1 : 0,
            'on_blood_thinners'  => isset($_POST['on_blood_thinners']) ? 1 : 0,
            'has_autoimmune'     => isset($_POST['has_autoimmune']) ? 1 : 0,
            'had_prior_reaction' => isset($_POST['had_prior_reaction']) ? 1 : 0,
            'smoker'             => isset($_POST['smoker']) ? 1 : 0,
            'prior_treatments'   => $this->input('prior_treatments'),
            'reviewed_by'        => Auth::id(),
            'reviewed_at'        => date('Y-m-d H:i:s'),
        ];

        if ($exists) {
            DB::update('medical_histories', (int) $exists['id'], $data);
        } else {
            DB::insert('medical_histories', $data + ['patient_id' => $id]);
        }

        Audit::log('update', 'medical_history', $id);
        Flash::add('success', 'Medical history updated.');
        $this->redirect("/patients/$id");
    }

    public function photo(int $id): void
    {
        Csrf::verify();
        $cfg = $GLOBALS['config']['uploads'];
        $file = $_FILES['photo'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Flash::add('error', 'No file was uploaded.');
            $this->redirect("/patients/$id");
        }
        if ($file['size'] > $cfg['max_mb'] * 1024 * 1024) {
            Flash::add('error', "Image must be under {$cfg['max_mb']}MB.");
            $this->redirect("/patients/$id");
        }

        $info = @getimagesize($file['tmp_name']);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!$info || !isset($allowed[$info['mime']])) {
            Flash::add('error', 'Only JPG, PNG or WebP images are accepted.');
            $this->redirect("/patients/$id");
        }

        if (!is_dir($cfg['photos'])) {
            @mkdir($cfg['photos'], 0775, true);
        }
        $name = $id . '-' . bin2hex(random_bytes(6)) . '.' . $allowed[$info['mime']];
        move_uploaded_file($file['tmp_name'], $cfg['photos'] . '/' . $name);

        DB::insert('patient_photos', [
            'patient_id'        => $id,
            'type'              => in_array($this->input('type'), ['before','after','progress'], true)
                                    ? $this->input('type') : 'before',
            'file_path'         => 'uploads/photos/' . $name,
            'marketing_consent' => isset($_POST['marketing_consent']) ? 1 : 0,
            'uploaded_by'       => Auth::id(),
        ]);

        Audit::log('upload', 'patient_photo', $id);
        Flash::add('success', 'Photo saved.');
        $this->redirect("/patients/$id");
    }
}
