<?php
/**
 * Route table.
 * The third argument is the permission required — the router enforces it
 * before the controller runs.
 *
 * @var App\Core\Router $router
 */

// --- authentication -------------------------------------------------
$router->get('/login',            'AuthController@showLogin');
$router->post('/login',           'AuthController@login');
$router->get('/logout',           'AuthController@logout');

// --- dashboard ------------------------------------------------------
$router->get('/',                 'DashboardController@index',        'dashboard.view');
$router->get('/dashboard',        'DashboardController@index',        'dashboard.view');

// --- diary ----------------------------------------------------------
$router->get('/appointments',             'AppointmentController@index',   'appointments.view');
$router->get('/appointments/create',      'AppointmentController@create',  'appointments.manage');
$router->post('/appointments',            'AppointmentController@store',   'appointments.manage');
$router->get('/appointments/{id}',        'AppointmentController@show',    'appointments.view');
$router->post('/appointments/{id}/status','AppointmentController@status',  'appointments.manage');
$router->post('/appointments/{id}/delete','AppointmentController@destroy', 'appointments.manage');

// --- patients -------------------------------------------------------
$router->get('/patients',               'PatientController@index',   'patients.view');
$router->get('/patients/create',        'PatientController@create',  'patients.manage');
$router->post('/patients',              'PatientController@store',   'patients.manage');
$router->get('/patients/{id}',          'PatientController@show',    'patients.view');
$router->get('/patients/{id}/edit',     'PatientController@edit',    'patients.manage');
$router->post('/patients/{id}',         'PatientController@update',  'patients.manage');
$router->post('/patients/{id}/history', 'PatientController@history', 'patients.clinical');
$router->post('/patients/{id}/photo',   'PatientController@photo',   'patients.clinical');

// --- clinical records -----------------------------------------------
$router->get('/records',                 'RecordController@index',  'records.view');
$router->get('/records/create',          'RecordController@create', 'records.manage');
$router->post('/records',                'RecordController@store',  'records.manage');
$router->get('/records/{id}',            'RecordController@show',   'records.view');

// --- consent --------------------------------------------------------
$router->get('/consents',                'ConsentController@index',  'consents.manage');
$router->get('/consents/create',         'ConsentController@create', 'consents.manage');
$router->post('/consents',               'ConsentController@store',  'consents.manage');
$router->get('/consents/{id}',           'ConsentController@show',   'consents.manage');

// --- follow-ups & adverse events ------------------------------------
$router->get('/followups',               'FollowUpController@index',   'followups.manage');
$router->post('/followups/{id}/done',    'FollowUpController@complete','followups.manage');
$router->get('/events',                  'EventController@index',      'events.manage');
$router->get('/events/create',           'EventController@create',     'events.manage');
$router->post('/events',                 'EventController@store',      'events.manage');

// --- treatment catalogue --------------------------------------------
$router->get('/treatments',              'TreatmentController@index',  'appointments.view');
$router->get('/treatments/create',       'TreatmentController@create', 'treatments.manage');
$router->post('/treatments',             'TreatmentController@store',  'treatments.manage');
$router->get('/treatments/{id}/edit',    'TreatmentController@edit',   'treatments.manage');
$router->post('/treatments/{id}',        'TreatmentController@update', 'treatments.manage');

// --- stock ----------------------------------------------------------
$router->get('/stock',                   'StockController@index',       'stock.view');
$router->get('/stock/products/create',   'StockController@createProduct','stock.manage');
$router->post('/stock/products',         'StockController@storeProduct', 'stock.manage');
$router->get('/stock/batches/create',    'StockController@createBatch',  'stock.manage');
$router->post('/stock/batches',          'StockController@storeBatch',   'stock.manage');
$router->post('/stock/waste',            'StockController@waste',        'stock.manage');
$router->get('/stock/movements',         'StockController@movements',    'stock.view');

// --- billing --------------------------------------------------------
$router->get('/invoices',                'InvoiceController@index',   'billing.view');
$router->get('/invoices/create',         'InvoiceController@create',  'billing.manage');
$router->post('/invoices',               'InvoiceController@store',   'billing.manage');
$router->get('/invoices/{id}',           'InvoiceController@show',    'billing.view');
$router->post('/invoices/{id}/pay',      'InvoiceController@pay',     'billing.manage');

// --- practitioners & staff ------------------------------------------
$router->get('/practitioners',           'PractitionerController@index',  'appointments.view');
$router->get('/practitioners/{id}',      'PractitionerController@show',   'appointments.view');
$router->get('/staff',                   'StaffController@index',         'staff.manage');
$router->get('/staff/create',            'StaffController@create',        'staff.manage');
$router->post('/staff',                  'StaffController@store',         'staff.manage');
$router->post('/staff/{id}/toggle',      'StaffController@toggle',        'staff.manage');

// --- reports, settings, audit ---------------------------------------
$router->get('/reports',                 'ReportController@index',   'reports.view');
$router->get('/settings',                'SettingsController@index', 'settings.manage');
$router->post('/settings',               'SettingsController@save',  'settings.manage');
$router->get('/audit',                   'AuditController@index',    'audit.view');
