<?php

namespace App\Controllers;

use App\Core\Audit;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database as DB;
use App\Core\Flash;

final class SettingsController extends Controller
{
    private const KEYS = [
        'clinic_name'       => 'Clinic name',
        'clinic_tagline'    => 'Tagline',
        'clinic_phone'      => 'Phone',
        'clinic_email'      => 'Email',
        'clinic_address'    => 'Address',
        'currency_symbol'   => 'Currency symbol',
        'topup_window_days' => 'Free top-up window (days)',
    ];

    public function index(): void
    {
        $rows = DB::all('SELECT `key`, `value` FROM settings');
        $this->view('settings/index', [
            'title'    => 'Settings',
            'settings' => array_column($rows, 'value', 'key'),
            'keys'     => self::KEYS,
        ]);
    }

    public function save(): void
    {
        Csrf::verify();
        foreach (array_keys(self::KEYS) as $key) {
            $value = $this->input($key, '');
            DB::run(
                'INSERT INTO settings (`key`, `value`) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
                [$key, $value]
            );
        }
        Audit::log('update', 'settings');
        Flash::add('success', 'Settings saved.');
        $this->redirect('/settings');
    }
}
