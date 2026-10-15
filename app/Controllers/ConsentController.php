<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

final class ConsentController extends Controller
{
    private const BODY = <<<'TXT'
I confirm that the nature of the treatment, its expected results and its limitations have
been explained to me in a way I understand.

I have been told about the common side effects — swelling, bruising, tenderness, redness
and temporary asymmetry — and about rare but serious risks including infection, nodules,
allergic reaction and, with dermal fillers, vascular occlusion.

I confirm that the medical history I have given is complete and accurate, including any
medication, allergies, pregnancy or breastfeeding.

I understand that results vary between individuals, that no outcome is guaranteed, and
that further sessions may be needed at additional cost.

I have had the opportunity to ask questions and they have been answered.
TXT;

    public function index(): void
    {
        $this->view('consents/index', [
            'title' => 'Consent forms',
            'consents' => DB::all(
                'SELECT c.*, p.full_name AS patient_name, p.code, t.name AS treatment_name
                   FROM consents c
                   JOIN patients p ON p.id = c.patient_id
                   JOIN treatments t ON t.id = c.treatment_id
                  ORDER BY c.id DESC LIMIT 120'
            ),
        ]);
    }

    public function create(): void
    {
        $this->view('consents/form', [
            'title'      => 'New consent form',
            'body'       => self::BODY,
            'patients'   => DB::all('SELECT id, code, full_name FROM patients ORDER BY full_name'),
            'treatments' => DB::all('SELECT * FROM treatments WHERE requires_consent = 1 AND is_active = 1 ORDER BY name'),
            'prefill'    => [
                'patient_id'     => (int) ($this->query('patient') ?? 0),
                'treatment_id'   => (int) ($this->query('treatment') ?? 0),
                'appointment_id' => (int) ($this->query('appointment') ?? 0),
            ],
        ]);
    }

    public function store(): void
    {
        Csrf::verify();

        $v = $this->validate([
            'patient_id'   => 'required|numeric',
            'treatment_id' => 'required|numeric',
            'signed_name'  => 'required|max:140',
        ], ['signed_name' => 'Patient signature name']);

        if ($v->fails()) {
            Flash::add('error', $v->firstError());
            $this->redirect('/consents/create');
        }

        if (!isset($_POST['agree'])) {
            Flash::add('error', 'The patient must confirm they agree before the form can be stored.');
            $this->redirect('/consents/create');
        }

        $id = DB::insert('consents', [
            'patient_id'     => $this->intInput('patient_id'),
            'appointment_id' => $this->intInput('appointment_id') ?: null,
            'treatment_id'   => $this->intInput('treatment_id'),
            'version'        => '1.0',
            'body_snapshot'  => self::BODY,
            'signed_name'    => $this->input('signed_name'),
            'signed_at'      => date('Y-m-d H:i:s'),
            'photo_consent'  => isset($_POST['photo_consent']) ? 1 : 0,
            'witnessed_by'   => Auth::id(),
            'ip_address'     => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);

        Audit::log('sign', 'consent', $id);
        Flash::add('success', 'Consent recorded and stored against the patient record.');

        $appt = $this->intInput('appointment_id');
        $this->redirect($appt ? "/appointments/$appt" : "/consents/$id");
    }

    public function show(int $id): void
    {
        $consent = DB::one(
            'SELECT c.*, p.full_name AS patient_name, p.code, p.date_of_birth,
                    t.name AS treatment_name, u.full_name AS witness_name
               FROM consents c
               JOIN patients p ON p.id = c.patient_id
               JOIN treatments t ON t.id = c.treatment_id
               LEFT JOIN users u ON u.id = c.witnessed_by
              WHERE c.id = ?', [$id]
        );
        if (!$consent) {
            $this->notFound('Consent form');
        }
        $this->view('consents/show', ['title' => 'Consent form', 'consent' => $consent]);
    }
}
