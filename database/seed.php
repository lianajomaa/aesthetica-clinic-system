<?php
/**
 * Seeds the database with a realistic working clinic:
 * staff, patients, stock with lot numbers, a few months of history,
 * today's schedule and upcoming bookings.
 *
 *   php database/seed.php
 */

$dsn  = getenv('DB_DSN')  ?: 'mysql:host=127.0.0.1;dbname=aesthetica;charset=utf8mb4';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';

$db = new PDO($dsn, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

function ins(PDO $db, string $table, array $row): int {
    $cols = array_keys($row);
    $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES ("
         . implode(',', array_fill(0, count($cols), '?')) . ")";
    $db->prepare($sql)->execute(array_values($row));
    return (int)$db->lastInsertId();
}

$db->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['audit_log','payments','invoice_items','invoices','adverse_events','follow_ups',
          'patient_photos','treatment_record_items','treatment_records','consents','consultations',
          'appointments','treatment_products','treatments','aftercare_templates','stock_movements',
          'batches','products','suppliers','medical_histories','patients','time_off',
          'practitioner_schedules','practitioners','users','role_permissions','permissions','roles',
          'settings'] as $t) {
    $db->exec("TRUNCATE TABLE `$t`");
}
$db->exec('SET FOREIGN_KEY_CHECKS = 1');

/* ---------------- roles & permissions ---------------- */

$roles = [
    'owner'        => ['Owner / Admin', 'Full access including finance and settings'],
    'doctor'       => ['Doctor / Injector', 'Clinical records, own calendar, treatments'],
    'nurse'        => ['Nurse / Assistant', 'Assists, photos, medical history'],
    'receptionist' => ['Receptionist', 'Bookings, check-in, payments — no clinical notes'],
    'accountant'   => ['Accountant', 'Financial reports only'],
];
$roleId = [];
foreach ($roles as $slug => [$name, $desc]) {
    $roleId[$slug] = ins($db, 'roles', ['slug' => $slug, 'name' => $name, 'description' => $desc]);
}

$permissions = [
    'dashboard.view'      => ['General',   'View dashboard'],
    'patients.view'       => ['Patients',  'View patients'],
    'patients.manage'     => ['Patients',  'Create and edit patients'],
    'patients.clinical'   => ['Patients',  'View clinical notes, history and photos'],
    'appointments.view'   => ['Diary',     'View the diary'],
    'appointments.manage' => ['Diary',     'Book, move and cancel appointments'],
    'records.view'        => ['Clinical',  'View treatment records'],
    'records.manage'      => ['Clinical',  'Record treatments performed'],
    'consents.manage'     => ['Clinical',  'Issue and sign consent forms'],
    'followups.manage'    => ['Clinical',  'Manage follow-ups and recalls'],
    'events.manage'       => ['Clinical',  'Record adverse events'],
    'treatments.manage'   => ['Catalogue', 'Manage the treatment menu'],
    'stock.view'          => ['Stock',     'View stock and batches'],
    'stock.manage'        => ['Stock',     'Receive stock, record wastage'],
    'billing.view'        => ['Billing',   'View invoices'],
    'billing.manage'      => ['Billing',   'Create invoices and take payment'],
    'reports.view'        => ['Reports',   'View reports and revenue'],
    'staff.manage'        => ['Admin',     'Manage staff and practitioners'],
    'settings.manage'     => ['Admin',     'Change clinic settings'],
    'audit.view'          => ['Admin',     'View the audit log'],
];
$permId = [];
foreach ($permissions as $slug => [$area, $label]) {
    $permId[$slug] = ins($db, 'permissions', ['slug' => $slug, 'area' => $area, 'label' => $label]);
}

$grant = [
    'owner'        => array_keys($permissions),
    'doctor'       => ['dashboard.view','patients.view','patients.manage','patients.clinical',
                       'appointments.view','appointments.manage','records.view','records.manage',
                       'consents.manage','followups.manage','events.manage','stock.view','reports.view'],
    'nurse'        => ['dashboard.view','patients.view','patients.manage','patients.clinical',
                       'appointments.view','records.view','consents.manage','stock.view'],
    'receptionist' => ['dashboard.view','patients.view','patients.manage','appointments.view',
                       'appointments.manage','billing.view','billing.manage','consents.manage'],
    'accountant'   => ['dashboard.view','billing.view','reports.view'],
];
foreach ($grant as $role => $list) {
    foreach ($list as $p) {
        ins($db, 'role_permissions', ['role_id' => $roleId[$role], 'permission_id' => $permId[$p]]);
    }
}

/* ---------------- staff ---------------- */

$hash = password_hash('password', PASSWORD_DEFAULT);
$staff = [
    ['owner',        'Dr. Rana Hajjar',  'owner@aesthetica.test',    'RH', '+961 3 111 222'],
    ['doctor',       'Dr. Karim Nassar', 'karim@aesthetica.test',    'KN', '+961 3 333 444'],
    ['doctor',       'Dr. Lea Haddad',   'lea@aesthetica.test',      'LH', '+961 3 555 666'],
    ['nurse',        'Yara Chami',       'yara@aesthetica.test',     'YC', '+961 3 777 888'],
    ['receptionist', 'Nadine Rizk',      'reception@aesthetica.test','NR', '+961 3 999 000'],
];
$userId = [];
foreach ($staff as [$role, $name, $email, $initials, $phone]) {
    $userId[$email] = ins($db, 'users', [
        'role_id' => $roleId[$role], 'full_name' => $name, 'email' => $email,
        'password_hash' => $hash, 'phone' => $phone, 'avatar_initials' => $initials,
    ]);
}

$pracDefs = [
    ['owner@aesthetica.test', 'Dr.', 'Aesthetic medicine · injectables', 'LB-AM-2291', 0.00, '#1F6F52'],
    ['karim@aesthetica.test', 'Dr.', 'Dermatology · fillers',           'LB-DR-4417', 35.00, '#2F7F8F'],
    ['lea@aesthetica.test',   'Dr.', 'Facial aesthetics · threads',     'LB-AM-5530', 35.00, '#8A6BBF'],
];
$pracId = [];
foreach ($pracDefs as [$email, $title, $spec, $lic, $comm, $colour]) {
    $pracId[$email] = ins($db, 'practitioners', [
        'user_id' => $userId[$email], 'title' => $title, 'speciality' => $spec,
        'license_no' => $lic, 'commission_rate' => $comm, 'calendar_colour' => $colour,
    ]);
}
// Mon–Sat mornings and afternoons; Dr. Lea does not work Mondays.
foreach ($pracId as $email => $pid) {
    foreach ([1,2,3,4,5,6] as $wd) {
        if ($email === 'lea@aesthetica.test' && $wd === 1) continue;
        ins($db, 'practitioner_schedules', [
            'practitioner_id' => $pid, 'weekday' => $wd,
            'start_time' => '09:00:00', 'end_time' => $wd === 6 ? '14:00:00' : '18:00:00',
        ]);
    }
}
ins($db, 'time_off', [
    'practitioner_id' => $pracId['karim@aesthetica.test'],
    'starts_at' => date('Y-m-d 00:00:00', strtotime('+9 days')),
    'ends_at'   => date('Y-m-d 23:59:59', strtotime('+12 days')),
    'reason'    => 'Conference — Dubai Derma',
]);

/* ---------------- suppliers, products, batches ---------------- */

$supA = ins($db, 'suppliers', ['name' => 'MedAesthetic Lebanon', 'contact_name' => 'Ziad Aoun',
    'phone' => '+961 1 480 220', 'email' => 'orders@medaesthetic.lb']);
$supB = ins($db, 'suppliers', ['name' => 'DermaSupply Gulf', 'contact_name' => 'Hala Mroue',
    'phone' => '+971 4 221 9080', 'email' => 'sales@dermasupply.ae']);

$productDefs = [
    ['Botulinum Toxin 100u', 'Botox® Allergan',  'toxin',       'unit',  2.10, 150],
    ['Botulinum Toxin 100u', 'Dysport®',         'toxin',       'unit',  1.70, 100],
    ['Hyaluronic Filler 1ml','Juvéderm Ultra',   'filler',      'ml',  110.00,   4],
    ['Hyaluronic Filler 1ml','Restylane Kysse',  'filler',      'ml',  125.00,   3],
    ['PDO Thread — Cog',     'MINT Threads',     'thread',      'piece', 22.00,  12],
    ['Profhilo 2ml',         'IBSA',             'skinbooster', 'ml',   95.00,   3],
    ['Numbing Cream 30g',    'Emla',             'consumable',  'piece', 14.00,   5],
];
$prodId = [];
foreach ($productDefs as $i => [$name, $brand, $type, $unit, $cost, $reorder]) {
    $prodId[$i] = ins($db, 'products', [
        'name' => $name, 'brand' => $brand, 'type' => $type, 'unit' => $unit,
        'unit_cost' => $cost, 'reorder_level' => $reorder,
    ]);
}

$batchDefs = [
    // product index, lot, expiry, received, remaining
    [0, 'BX-4471-A', '+14 months', 1000, 612],
    [0, 'BX-5120-C', '+24 days',    500, 180],   // expiring soon — triggers an alert
    [1, 'DY-2208-B', '+10 months',  500, 410],
    [2, 'JUV-7741',  '+16 months',   12,   3],   // below reorder level
    [3, 'RST-3390',  '+13 months',    8,   5],
    [4, 'MNT-9921',  '+20 months',   60,  26],
    [5, 'PRO-1180',  '+11 months',   10,   6],
    [6, 'EML-5567',  '+18 months',   20,   9],
];
$batchId = [];
foreach ($batchDefs as $i => [$p, $lot, $exp, $recv, $rem]) {
    $batchId[$i] = ins($db, 'batches', [
        'product_id' => $prodId[$p], 'supplier_id' => $i % 2 ? $supB : $supA,
        'lot_number' => $lot, 'expiry_date' => date('Y-m-d', strtotime($exp)),
        'qty_received' => $recv, 'qty_remaining' => $rem,
        'received_at' => date('Y-m-d', strtotime('-' . (20 + $i * 9) . ' days')),
    ]);
    ins($db, 'stock_movements', [
        'batch_id' => $batchId[$i], 'type' => 'in', 'quantity' => $recv,
        'note' => 'Opening stock received', 'user_id' => $userId['owner@aesthetica.test'],
        'created_at' => date('Y-m-d H:i:s', strtotime('-' . (20 + $i * 9) . ' days')),
    ]);
}

/* ---------------- aftercare & treatments ---------------- */

$aftercare = [
    'Botulinum toxin — aftercare' =>
        "Stay upright for 4 hours.\nNo rubbing or massaging the treated area for 24 hours.\n" .
        "Avoid exercise, saunas and alcohol for 24 hours.\nFull effect appears at 10–14 days.\n" .
        "Attend your two-week review so any asymmetry can be corrected.",
    'Dermal filler — aftercare' =>
        "Expect swelling and possible bruising for 2–5 days.\nApply cool compresses, not ice directly.\n" .
        "No makeup on the area for 12 hours.\nAvoid heat, exercise and alcohol for 24 hours.\n" .
        "Contact the clinic immediately if you notice blanching, severe pain or skin discolouration.",
    'PDO threads — aftercare' =>
        "Sleep on your back, elevated, for 5 nights.\nNo wide mouth opening or dental work for 2 weeks.\n" .
        "No facial massage or treatments for 4 weeks.\nMild tightness and puckering is normal early on.",
    'Skin booster — aftercare' =>
        "Small bumps settle within 24–48 hours.\nNo makeup for 12 hours.\n" .
        "Avoid sun, sauna and strenuous exercise for 48 hours.\nSecond session is due in 4 weeks.",
];
$acId = [];
foreach ($aftercare as $title => $body) {
    $acId[$title] = ins($db, 'aftercare_templates', ['title' => $title, 'body' => $body]);
}

$treatmentDefs = [
    // name, category, minutes, price, price_type, consent, followup, recall, aftercare
    ['Consultation',                'consultation', 30,   0.00, 'fixed',    0, null, null, null],
    ['Botox — upper face',          'toxin',        30,  12.00, 'per_unit', 1,  14, 120, 'Botulinum toxin — aftercare'],
    ['Botox — forehead & glabella', 'toxin',        30,  12.00, 'per_unit', 1,  14, 120, 'Botulinum toxin — aftercare'],
    ['Botox — crow’s feet',         'toxin',        25,  12.00, 'per_unit', 1,  14, 120, 'Botulinum toxin — aftercare'],
    ['Masseter / jaw slimming',     'toxin',        30,  12.00, 'per_unit', 1,  21, 150, 'Botulinum toxin — aftercare'],
    ['Lip filler — 1ml',            'filler',       45, 450.00, 'fixed',    1,  14, 270, 'Dermal filler — aftercare'],
    ['Cheek filler — 1ml',          'filler',       45, 520.00, 'fixed',    1,  14, 300, 'Dermal filler — aftercare'],
    ['Nasolabial folds — 1ml',      'filler',       40, 480.00, 'fixed',    1,  14, 270, 'Dermal filler — aftercare'],
    ['PDO threads — jawline',       'thread',       60, 780.00, 'fixed',    1,  21, 365, 'PDO threads — aftercare'],
    ['Profhilo — skin booster',     'skinbooster',  30, 390.00, 'fixed',    1,  28, 180, 'Skin booster — aftercare'],
];
$treatId = [];
foreach ($treatmentDefs as $i => [$n, $cat, $dur, $price, $pt, $con, $fu, $rc, $ac]) {
    $treatId[$i] = ins($db, 'treatments', [
        'name' => $n, 'category' => $cat, 'duration_min' => $dur, 'price' => $price,
        'price_type' => $pt, 'requires_consent' => $con, 'followup_days' => $fu,
        'recall_days' => $rc, 'aftercare_id' => $ac ? $acId[$ac] : null,
    ]);
}
foreach ([1 => 0, 2 => 0, 3 => 0, 4 => 0] as $t => $p) {
    ins($db, 'treatment_products', ['treatment_id' => $treatId[$t], 'product_id' => $prodId[$p], 'default_qty' => 24]);
}
foreach ([5 => 2, 6 => 2, 7 => 3] as $t => $p) {
    ins($db, 'treatment_products', ['treatment_id' => $treatId[$t], 'product_id' => $prodId[$p], 'default_qty' => 1]);
}
ins($db, 'treatment_products', ['treatment_id' => $treatId[8], 'product_id' => $prodId[4], 'default_qty' => 6]);
ins($db, 'treatment_products', ['treatment_id' => $treatId[9], 'product_id' => $prodId[5], 'default_qty' => 2]);

/* ---------------- patients ---------------- */

$patientDefs = [
    ['Rana Haidar',    '1989-04-12', '+961 70 441 220', 'instagram'],
    ['Maya Khalil',    '1996-11-02', '+961 71 338 907', 'instagram'],
    ['Lara Saad',      '1992-02-24', '+961 76 220 118', 'referral'],
    ['Nour Ayoub',     '1985-07-30', '+961 3 889 441',  'referral'],
    ['Zeina Fares',    '1998-09-15', '+961 81 556 230', 'instagram'],
    ['Sara Daher',     '1990-01-08', '+961 70 912 004', 'google'],
    ['Hiba Mansour',   '1983-05-19', '+961 71 664 882', 'walk_in'],
    ['Yasmine Traboulsi','1994-12-03','+961 76 101 559', 'instagram'],
    ['Dima Chehab',    '1987-08-21', '+961 3 447 219',  'referral'],
    ['Carla Semaan',   '1991-06-11', '+961 70 775 330', 'facebook'],
    ['Rita Khoury',    '1979-03-27', '+961 71 229 806', 'referral'],
    ['Joelle Abi Rached','1997-10-09','+961 76 554 018', 'instagram'],
    ['Nadia Fakhoury', '1982-12-16', '+961 3 660 147',  'google'],
    ['Perla Ghanem',   '1999-05-05', '+961 81 330 992', 'instagram'],
];
$patId = [];
foreach ($patientDefs as $i => [$name, $dob, $phone, $src]) {
    $patId[$i] = ins($db, 'patients', [
        'code' => sprintf('PT-%05d', 100 + $i), 'full_name' => $name, 'date_of_birth' => $dob,
        'gender' => 'female', 'phone' => $phone, 'source' => $src,
        'email' => strtolower(str_replace([' ', '’'], ['.', ''], $name)) . '@example.com',
        'created_by' => $userId['reception@aesthetica.test'],
        'created_at' => date('Y-m-d H:i:s', strtotime('-' . (200 - $i * 11) . ' days')),
    ]);
    ins($db, 'medical_histories', [
        'patient_id' => $patId[$i],
        'allergies' => $i % 5 === 0 ? 'Penicillin' : 'None known',
        'medications' => $i % 4 === 0 ? 'Aspirin 75mg daily' : 'None',
        'conditions' => $i % 7 === 0 ? 'Hypothyroidism — controlled' : 'None',
        'is_pregnant' => 0,
        'is_breastfeeding' => $i === 11 ? 1 : 0,
        'on_blood_thinners' => $i % 4 === 0 ? 1 : 0,
        'has_autoimmune' => $i === 6 ? 1 : 0,
        'had_prior_reaction' => 0,
        'prior_treatments' => $i % 3 === 0 ? 'Botox elsewhere, 2024' : null,
        'smoker' => $i % 6 === 0 ? 1 : 0,
        'reviewed_by' => $userId['yara@aesthetica.test'],
        'reviewed_at' => date('Y-m-d H:i:s', strtotime('-' . (190 - $i * 11) . ' days')),
    ]);
}

/* ---------------- history: appointments, records, invoices ---------------- */

$sites = [
    1 => ['Glabella', 'Left frontalis', 'Right frontalis'],
    2 => ['Glabella', 'Forehead — central', 'Forehead — lateral'],
    3 => ['Left orbicularis', 'Right orbicularis'],
    4 => ['Left masseter', 'Right masseter'],
    5 => ['Upper lip — body', 'Lower lip — body'],
    6 => ['Left malar', 'Right malar'],
    7 => ['Left nasolabial', 'Right nasolabial'],
    8 => ['Left jawline', 'Right jawline'],
    9 => ['Left cheek', 'Right cheek', 'Neck'],
];
$prodForTreat = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 2, 6 => 2, 7 => 3, 8 => 4, 9 => 5];
$batchForProd = [0 => 0, 2 => 3, 3 => 4, 4 => 5, 5 => 6];
$pracList = array_values($pracId);
$invoiceNo = 1000;

$takeStock = function (PDO $db, int $batch, float $qty, int $recId, int $uid) {
    $db->prepare('UPDATE batches SET qty_remaining = GREATEST(qty_remaining - ?, 0) WHERE id = ?')
       ->execute([$qty, $batch]);
    ins($db, 'stock_movements', [
        'batch_id' => $batch, 'type' => 'out', 'quantity' => $qty,
        'reference_type' => 'treatment_record', 'reference_id' => $recId,
        'note' => 'Used in treatment', 'user_id' => $uid,
    ]);
};

// 45 completed visits spread over the last 5 months
for ($v = 0; $v < 45; $v++) {
    $t    = [1,2,3,4,5,6,7,8,9][$v % 9];
    $pIdx = $v % count($patId);
    $prac = $pracList[$v % 3];
    $days = (int) round(76 - $v * 1.73);   // spreads from ~11 weeks ago up to today
    $when = date('Y-m-d', strtotime("-{$days} days")) . ' ' . sprintf('%02d:%02d:00', 9 + ($v % 8), ($v % 2) * 30);
    $dur  = $treatmentDefs[$t][2];

    $apptId = ins($db, 'appointments', [
        'patient_id' => $patId[$pIdx], 'practitioner_id' => $prac, 'treatment_id' => $treatId[$t],
        'starts_at' => $when, 'ends_at' => date('Y-m-d H:i:s', strtotime($when) + $dur * 60),
        'status' => 'completed', 'created_by' => $userId['reception@aesthetica.test'],
        'created_at' => date('Y-m-d H:i:s', strtotime($when) - 86400 * 3),
    ]);

    $isUnit = $treatmentDefs[$t][4] === 'per_unit';
    $recId = ins($db, 'treatment_records', [
        'appointment_id' => $apptId, 'patient_id' => $patId[$pIdx], 'practitioner_id' => $prac,
        'treatment_id' => $treatId[$t], 'performed_at' => $when,
        'technique' => $isUnit ? '30G needle · 2.5ml saline dilution' : ($t === 8 ? 'Blunt cannula 19G' : '27G cannula'),
        'notes' => 'Uneventful. Patient tolerated well.',
        'total_units' => 0, 'total_ml' => 0, 'created_at' => $when,
    ]);

    $prodIdx = $prodForTreat[$t];
    $bId = $batchId[$batchForProd[$prodIdx]];
    $unit = $productDefs[$prodIdx][3];
    $totU = 0; $totM = 0;
    foreach ($sites[$t] as $k => $site) {
        $qty = $unit === 'unit' ? (float)(8 + ($v + $k) % 6)
             : ($unit === 'piece' ? 2.0 : round(0.3 + (($v + $k) % 3) * 0.2, 1));
        ins($db, 'treatment_record_items', [
            'treatment_record_id' => $recId, 'product_id' => $prodId[$prodIdx], 'batch_id' => $bId,
            'injection_site' => $site, 'quantity' => $qty, 'unit' => $unit,
        ]);
        if ($unit === 'unit') $totU += $qty; elseif ($unit === 'ml') $totM += $qty;
        $takeStock($db, $bId, $qty, $recId, $userId['owner@aesthetica.test']);
    }
    $db->prepare('UPDATE treatment_records SET total_units=?, total_ml=? WHERE id=?')
       ->execute([$totU, $totM, $recId]);

    // consent
    ins($db, 'consents', [
        'patient_id' => $patId[$pIdx], 'appointment_id' => $apptId, 'treatment_id' => $treatId[$t],
        'body_snapshot' => 'I confirm the risks, benefits and alternatives have been explained to me.',
        'signed_name' => $patientDefs[$pIdx][0], 'signed_at' => $when,
        'photo_consent' => $v % 3 === 0 ? 1 : 0, 'witnessed_by' => $userId['yara@aesthetica.test'],
    ]);

    // invoice + payment
    $price = $treatmentDefs[$t][3];
    $total = $isUnit ? round($price * $totU, 2) : $price;
    $invId = ins($db, 'invoices', [
        'number' => 'INV-' . (++$invoiceNo), 'patient_id' => $patId[$pIdx], 'appointment_id' => $apptId,
        'issued_at' => $when, 'subtotal' => $total, 'discount' => 0, 'total' => $total,
        'amount_paid' => $total, 'status' => 'paid', 'created_by' => $userId['reception@aesthetica.test'],
    ]);
    ins($db, 'invoice_items', [
        'invoice_id' => $invId, 'description' => $treatmentDefs[$t][0] . ($isUnit ? " — {$totU} units" : ''),
        'quantity' => 1, 'unit_price' => $total, 'line_total' => $total,
    ]);
    ins($db, 'payments', [
        'invoice_id' => $invId, 'method' => ['cash','card','transfer'][$v % 3], 'amount' => $total,
        'paid_at' => $when, 'received_by' => $userId['reception@aesthetica.test'],
    ]);

    // follow-up
    if ($treatmentDefs[$t][6]) {
        $due = date('Y-m-d', strtotime($when) + $treatmentDefs[$t][6] * 86400);
        ins($db, 'follow_ups', [
            'patient_id' => $patId[$pIdx], 'treatment_record_id' => $recId, 'type' => 'review',
            'due_date' => $due, 'status' => $due < date('Y-m-d', strtotime('-7 days')) ? 'done' : 'pending',
            'notes' => 'Two-week review', 'completed_at' => null,
        ]);
    }
    if ($treatmentDefs[$t][7] && $v % 4 === 0) {
        ins($db, 'follow_ups', [
            'patient_id' => $patId[$pIdx], 'treatment_record_id' => $recId, 'type' => 'recall',
            'due_date' => date('Y-m-d', strtotime($when) + $treatmentDefs[$t][7] * 86400),
            'status' => 'pending', 'notes' => 'Due for repeat treatment',
        ]);
    }
}

// two adverse events
ins($db, 'adverse_events', [
    'patient_id' => $patId[3], 'type' => 'Bruising', 'severity' => 'mild',
    'description' => 'Bruising at left nasolabial injection point, noted at 48 hours.',
    'action_taken' => 'Reassurance, arnica advised. Resolved by day 7.', 'resolved' => 1,
    'reported_at' => date('Y-m-d H:i:s', strtotime('-38 days')),
    'reported_by' => $userId['karim@aesthetica.test'],
]);
ins($db, 'adverse_events', [
    'patient_id' => $patId[7], 'type' => 'Nodule', 'severity' => 'moderate',
    'description' => 'Palpable nodule upper lip, 2 weeks post filler.',
    'action_taken' => 'Massage demonstrated; reviewed at 4 weeks. Hyaluronidase discussed, not required.',
    'resolved' => 0, 'reported_at' => date('Y-m-d H:i:s', strtotime('-11 days')),
    'reported_by' => $userId['owner@aesthetica.test'],
]);

/* ---------------- today & the week ahead ---------------- */

$today = date('Y-m-d');
$todayList = [
    ['09:30', 0, 2, 0, 'completed'],
    ['10:15', 1, 0, 0, 'in_room'],
    ['11:00', 2, 5, 1, 'confirmed'],
    ['13:30', 3, 8, 2, 'confirmed'],
    ['15:00', 4, 9, 0, 'booked'],
    ['16:30', 5, 3, 1, 'confirmed'],
    ['17:15', 9, 6, 2, 'booked'],
];
foreach ($todayList as $idx => [$time, $pIdx, $t, $pr, $status]) {
    $start = "$today $time:00";
    $apptId = ins($db, 'appointments', [
        'patient_id' => $patId[$pIdx], 'practitioner_id' => $pracList[$pr],
        'treatment_id' => $treatId[$t], 'starts_at' => $start,
        'ends_at' => date('Y-m-d H:i:s', strtotime($start) + $treatmentDefs[$t][2] * 60),
        'status' => $status, 'deposit' => $t === 5 ? 100 : 0,
        'created_by' => $userId['reception@aesthetica.test'],
    ]);

    // Consent is signed for most of today's list; two are deliberately left
    // outstanding so the dashboard has something real to flag.
    if ($treatmentDefs[$t][5] === 1 && !in_array($idx, [4, 6], true)) {
        ins($db, 'consents', [
            'patient_id' => $patId[$pIdx], 'appointment_id' => $apptId,
            'treatment_id' => $treatId[$t],
            'body_snapshot' => 'I confirm the risks, benefits and alternatives have been explained to me.',
            'signed_name' => $patientDefs[$pIdx][0], 'signed_at' => "$today $time:00",
            'photo_consent' => $idx % 2, 'witnessed_by' => $userId['yara@aesthetica.test'],
        ]);
    }
}
for ($d = 1; $d <= 12; $d++) {
    $date = date('Y-m-d', strtotime("+{$d} days"));
    if (date('w', strtotime($date)) == 0) continue;           // closed Sundays
    $n = 2 + ($d % 3);
    for ($k = 0; $k < $n; $k++) {
        $t = [1,2,5,6,8,9,0][($d + $k) % 7];
        $start = $date . ' ' . sprintf('%02d:%02d:00', 9 + (($d + $k) % 7), (($k % 2) * 30));
        ins($db, 'appointments', [
            'patient_id' => $patId[($d * 3 + $k) % count($patId)],
            'practitioner_id' => $pracList[($d + $k) % 3],
            'treatment_id' => $treatId[$t], 'starts_at' => $start,
            'ends_at' => date('Y-m-d H:i:s', strtotime($start) + $treatmentDefs[$t][2] * 60),
            'status' => $d <= 3 ? 'confirmed' : 'booked',
            'created_by' => $userId['reception@aesthetica.test'],
        ]);
    }
}

/* ---------------- tidy stock so the demo starts healthy ---------------- */
// The 45 historical treatments drained the batches. Reset them to realistic
// levels: everything comfortable except one low product and one expiring lot.
$levels = [0 => 760, 1 => 180, 2 => 455, 3 => 2, 4 => 6, 5 => 31, 6 => 7, 7 => 12];
foreach ($levels as $i => $remaining) {
    $db->prepare('UPDATE batches SET qty_remaining = ? WHERE id = ?')
       ->execute([$remaining, $batchId[$i]]);
}

/* ---------------- settings ---------------- */

foreach ([
    'clinic_name'      => 'Aesthetica Clinic',
    'clinic_tagline'   => 'Aesthetic medicine & injectables',
    'clinic_phone'     => '+961 1 555 330',
    'clinic_email'     => 'hello@aesthetica.test',
    'clinic_address'   => 'Verdun, Beirut, Lebanon',
    'currency'         => 'USD',
    'currency_symbol'  => '$',
    'topup_window_days'=> '21',
] as $k => $v) {
    ins($db, 'settings', ['key' => $k, 'value' => $v]);
}

$counts = [];
foreach (['users','practitioners','patients','treatments','products','batches','appointments',
          'treatment_records','treatment_record_items','invoices','payments','follow_ups'] as $t) {
    $counts[$t] = $db->query("SELECT COUNT(*) c FROM `$t`")->fetch()['c'];
}
echo "Seeded:\n";
foreach ($counts as $t => $c) printf("  %-24s %s\n", $t, $c);
