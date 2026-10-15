<?php
use App\Core\Auth;
use App\Core\Flash;

$u = Auth::user();
$path = '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '', '/');
$base = rtrim(url('/'), '/');
if ($base !== '' && str_starts_with($path, $base)) {
    $path = '/' . trim(substr($path, strlen($base)), '/');
}
$on = static fn (string $p): string => ($p === '/' ? $path === '/' : str_starts_with($path, $p)) ? ' class="on"' : '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? 'Dashboard') ?> · <?= e(setting('clinic_name', 'Aesthetica')) ?></title>
<link rel="icon" href="<?= url('assets/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head>
<body>
<div class="shell">

  <aside class="side" id="side">
    <div class="brand">
      <div class="mark">AE</div>
      <div>
        <b><?= e(setting('clinic_name', 'Aesthetica')) ?></b>
        <small><?= e(setting('clinic_tagline', 'Clinic Suite')) ?></small>
      </div>
    </div>

    <nav class="nav">
      <a href="<?= url('/dashboard') ?>"<?= $on('/dashboard') ?>>
        <svg class="ico" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
        Dashboard</a>

      <?php if (Auth::can('appointments.view')): ?>
      <a href="<?= url('/appointments') ?>"<?= $on('/appointments') ?>>
        <svg class="ico" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
        Diary</a>
      <?php endif; ?>

      <?php if (Auth::can('patients.view')): ?>
      <a href="<?= url('/patients') ?>"<?= $on('/patients') ?>>
        <svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.6 3.1-6 7-6s7 2.4 7 6"/></svg>
        Patients</a>
      <?php endif; ?>

      <div class="navsec">Clinical</div>

      <?php if (Auth::can('records.view')): ?>
      <a href="<?= url('/records') ?>"<?= $on('/records') ?>>
        <svg class="ico" viewBox="0 0 24 24"><path d="M14 3l7 7-9 9H5v-7z"/><path d="M11 6l7 7"/></svg>
        Treatment records</a>
      <?php endif; ?>

      <?php if (Auth::can('consents.manage')): ?>
      <a href="<?= url('/consents') ?>"<?= $on('/consents') ?>>
        <svg class="ico" viewBox="0 0 24 24"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/></svg>
        Consent forms</a>
      <?php endif; ?>

      <?php if (Auth::can('followups.manage')):
          $due = (int) \App\Core\Database::value(
              "SELECT COUNT(*) FROM follow_ups WHERE status='pending' AND due_date <= CURDATE()"); ?>
      <a href="<?= url('/followups') ?>"<?= $on('/followups') ?>>
        <svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        Follow-ups<?php if ($due): ?><span class="count"><?= $due ?></span><?php endif; ?></a>
      <?php endif; ?>

      <?php if (Auth::can('events.manage')): ?>
      <a href="<?= url('/events') ?>"<?= $on('/events') ?>>
        <svg class="ico" viewBox="0 0 24 24"><path d="M12 3l9 16H3z"/><path d="M12 9v4M12 16v.5"/></svg>
        Adverse events</a>
      <?php endif; ?>

      <a href="<?= url('/treatments') ?>"<?= $on('/treatments') ?>>
        <svg class="ico" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
        Treatment menu</a>

      <div class="navsec">Practice</div>

      <?php if (Auth::can('stock.view')): ?>
      <a href="<?= url('/stock') ?>"<?= $on('/stock') ?>>
        <svg class="ico" viewBox="0 0 24 24"><path d="M7 3h10v4l-3 4v9H10v-9L7 7z"/></svg>
        Stock &amp; vials</a>
      <?php endif; ?>

      <?php if (Auth::can('billing.view')): ?>
      <a href="<?= url('/invoices') ?>"<?= $on('/invoices') ?>>
        <svg class="ico" viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 8h6M9 12h6"/></svg>
        Invoices</a>
      <?php endif; ?>

      <a href="<?= url('/practitioners') ?>"<?= $on('/practitioners') ?>>
        <svg class="ico" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 19c0-3 2.7-5 6-5s6 2 6 5M15 19c0-2 1-3.4 3-3.4s3 1.4 3 3.4"/></svg>
        Practitioners</a>

      <?php if (Auth::can('reports.view')): ?>
      <a href="<?= url('/reports') ?>"<?= $on('/reports') ?>>
        <svg class="ico" viewBox="0 0 24 24"><path d="M4 19V9M10 19V4M16 19v-7M22 19H2"/></svg>
        Reports</a>
      <?php endif; ?>

      <?php if (Auth::can('staff.manage')): ?>
      <div class="navsec">Admin</div>
      <a href="<?= url('/staff') ?>"<?= $on('/staff') ?>>
        <svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.6 3.1-6 7-6s7 2.4 7 6"/></svg>
        Staff</a>
      <a href="<?= url('/settings') ?>"<?= $on('/settings') ?>>
        <svg class="ico" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/></svg>
        Settings</a>
      <a href="<?= url('/audit') ?>"<?= $on('/audit') ?>>
        <svg class="ico" viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="2"/><path d="M8 9h8M8 13h8M8 17h5"/></svg>
        Audit log</a>
      <?php endif; ?>
    </nav>

    <div class="sideuser">
      <div class="av"><?= e($u['avatar_initials'] ?: initials($u['full_name'])) ?></div>
      <div>
        <div class="nm"><?= e($u['full_name']) ?></div>
        <div class="rl"><?= e($u['role_name']) ?></div>
      </div>
      <a href="<?= url('/logout') ?>" title="Sign out">
        <svg class="ico" viewBox="0 0 24 24" style="width:18px;height:18px"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
      </a>
    </div>
  </aside>
  <div class="scrim" id="scrim"></div>

  <div style="min-width:0">
    <header class="top">
      <button class="burger" id="burger" aria-label="Menu">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
      </button>
      <div class="where"><?= e($title ?? 'Dashboard') ?></div>
      <div class="right">
        <span class="today"><?= date('l j F Y') ?></span>
        <?php if (Auth::can('appointments.manage')): ?>
          <a class="btn sm" href="<?= url('/appointments/create') ?>">+ Appointment</a>
        <?php endif; ?>
      </div>
    </header>

    <main class="main">
      <?php foreach (Flash::take() as $f): ?>
        <div class="flash <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
      <?php endforeach; ?>
      <?= $content ?>
    </main>
  </div>
</div>

<script>
  var b = document.getElementById('burger'), s = document.getElementById('side'), sc = document.getElementById('scrim');
  if (b) {
    b.addEventListener('click', function () { s.classList.toggle('open'); sc.classList.toggle('on'); });
    sc.addEventListener('click', function () { s.classList.remove('open'); sc.classList.remove('on'); });
  }
</script>
</body>
</html>
