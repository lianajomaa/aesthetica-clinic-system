<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database as DB;

final class AuditController extends Controller
{
    public function index(): void
    {
        $this->view('audit/index', [
            'title' => 'Audit log',
            'entries' => DB::all(
                'SELECT a.*, u.full_name, r.name AS role_name
                   FROM audit_log a
                   LEFT JOIN users u ON u.id = a.user_id
                   LEFT JOIN roles r ON r.id = u.role_id
                  ORDER BY a.created_at DESC, a.id DESC LIMIT 250'
            ),
        ]);
    }
}
