<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

final class StaffController extends Controller
{
    public function index(): void
    {
        $this->view('staff/index', [
            'title' => 'Staff',
            'staff' => DB::all(
                'SELECT u.*, r.name AS role_name, r.slug AS role_slug,
                        (SELECT id FROM practitioners pr WHERE pr.user_id = u.id) AS practitioner_id
                   FROM users u JOIN roles r ON r.id = u.role_id
                  ORDER BY r.id, u.full_name'
            ),
            'roles' => DB::all('SELECT * FROM roles ORDER BY id'),
        ]);
    }

    public function create(): void
    {
        $this->view('staff/form', [
            'title' => 'Add a staff member',
            'roles' => DB::all('SELECT * FROM roles ORDER BY id'),
        ]);
    }

    public function store(): void
    {
        Csrf::verify();
        $v = $this->validate([
            'full_name' => 'required|max:120',
            'email'     => 'required|email',
            'password'  => 'required|min:8',
            'role_id'   => 'required|numeric',
        ], ['full_name' => 'Full name', 'role_id' => 'Role']);

        if ($v->fails()) {
            Flash::add('error', $v->firstError());
            $this->redirect('/staff/create');
        }

        if (DB::one('SELECT id FROM users WHERE email = ?', [$this->input('email')])) {
            Flash::add('error', 'That email address is already in use.');
            $this->redirect('/staff/create');
        }

        $name = $this->input('full_name');
        $parts = preg_split('/\s+/', trim(str_replace('Dr.', '', $name))) ?: [];
        $initials = strtoupper(substr($parts[0] ?? 'X', 0, 1) . substr($parts[1] ?? '', 0, 1));

        $userId = DB::insert('users', [
            'role_id'         => $this->intInput('role_id'),
            'full_name'       => $name,
            'email'           => $this->input('email'),
            'password_hash'   => password_hash($this->input('password'), PASSWORD_DEFAULT),
            'phone'           => $this->input('phone') ?: null,
            'avatar_initials' => $initials,
        ]);

        // A doctor also gets a practitioner profile and a default week.
        $role = DB::one('SELECT slug FROM roles WHERE id = ?', [$this->intInput('role_id')]);
        if (in_array($role['slug'] ?? '', ['doctor', 'owner'], true) && isset($_POST['is_practitioner'])) {
            $pracId = DB::insert('practitioners', [
                'user_id'    => $userId,
                'title'      => $this->input('title', 'Dr.'),
                'speciality' => $this->input('speciality') ?: null,
                'license_no' => $this->input('license_no') ?: null,
                'commission_rate' => $this->floatInput('commission_rate'),
                'calendar_colour' => $this->input('calendar_colour', '#1F6F52'),
            ]);
            foreach ([1,2,3,4,5] as $wd) {
                DB::insert('practitioner_schedules', [
                    'practitioner_id' => $pracId, 'weekday' => $wd,
                    'start_time' => '09:00:00', 'end_time' => '18:00:00',
                ]);
            }
        }

        Audit::log('create', 'user', $userId, $name);
        Flash::add('success', 'Staff member added.');
        $this->redirect('/staff');
    }

    public function toggle(int $id): void
    {
        Csrf::verify();
        $user = DB::one('SELECT is_active FROM users WHERE id = ?', [$id]);
        if (!$user) {
            $this->notFound('User');
        }
        DB::update('users', $id, ['is_active' => $user['is_active'] ? 0 : 1]);
        Audit::log('toggle', 'user', $id, $user['is_active'] ? 'deactivated' : 'activated');
        Flash::add('success', 'Account updated.');
        $this->redirect('/staff');
    }
}
