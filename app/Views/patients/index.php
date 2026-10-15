<?php use App\Core\Auth; ?>

<div class="ph">
  <div><h1>Patients</h1><p class="lead"><?= count($patients) ?> shown</p></div>
  <div class="actions">
    <form method="get" action="<?= url('/patients') ?>" style="display:flex;gap:8px">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Name, phone or code" style="width:230px">
      <button class="btn ghost" type="submit">Search</button>
    </form>
    <?php if (Auth::can('patients.manage')): ?>
      <a class="btn" href="<?= url('/patients/create') ?>">+ New patient</a>
    <?php endif; ?>
  </div>
</div>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>Patient</th><th>Phone</th><th>Source</th><th class="num">Visits</th><th>Last visit</th></tr></thead>
    <tbody>
    <?php if (!$patients): ?>
      <tr><td colspan="5" class="empty">No patients found<?= $q !== '' ? ' for “' . e($q) . '”' : '' ?>.</td></tr>
    <?php endif; ?>
    <?php foreach ($patients as $p): ?>
      <tr>
        <td><a class="who" href="<?= url('/patients/' . $p['id']) ?>">
          <span class="av"><?= e(initials($p['full_name'])) ?></span>
          <span><span class="nm"><?= e($p['full_name']) ?></span><small><?= e($p['code']) ?></small></span>
        </a></td>
        <td><?= e($p['phone']) ?></td>
        <td><span class="pill muted"><?= e(label($p['source'])) ?></span></td>
        <td class="num"><?= (int) $p['visits'] ?></td>
        <td><?= $p['last_visit'] ? d($p['last_visit']) : '—' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
