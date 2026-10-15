<?php use App\Core\Auth; ?>

<div class="ph">
  <div><h1>Treatment records</h1>
    <p class="lead">Every dose recorded by site, product and batch</p></div>
  <?php if (Auth::can('records.manage')): ?>
    <div class="actions"><a class="btn" href="<?= url('/records/create') ?>">+ Record a treatment</a></div>
  <?php endif; ?>
</div>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>Date</th><th>Patient</th><th>Treatment</th><th>Practitioner</th><th class="num">Dose</th></tr></thead>
    <tbody>
    <?php if (!$records): ?><tr><td colspan="5" class="empty">Nothing recorded yet.</td></tr><?php endif; ?>
    <?php foreach ($records as $r): ?>
      <tr>
        <td class="time"><a href="<?= url('/records/' . $r['id']) ?>"><?= d($r['performed_at']) ?></a>
          <small style="display:block;color:var(--faint);font-weight:400"><?= t($r['performed_at']) ?></small></td>
        <td><a class="who" href="<?= url('/patients/' . $r['patient_id']) ?>">
          <span class="av"><?= e(initials($r['patient_name'])) ?></span>
          <span><span class="nm"><?= e($r['patient_name']) ?></span><small><?= e($r['patient_code']) ?></small></span>
        </a></td>
        <td><?= e($r['treatment_name']) ?>
          <small style="display:block;color:var(--faint)"><?= e(label($r['category'])) ?></small></td>
        <td><?= e($r['practitioner_name']) ?></td>
        <td class="num">
          <?= (float) $r['total_units'] > 0 ? '<strong>' . qty($r['total_units']) . '</strong> units' : '' ?>
          <?= (float) $r['total_ml'] > 0 ? '<strong>' . qty($r['total_ml']) . '</strong> ml' : '' ?>
          <?= ((float) $r['total_units'] + (float) $r['total_ml']) == 0 ? '—' : '' ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
