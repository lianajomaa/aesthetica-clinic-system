<?php use App\Core\Auth; $p = $prac; ?>

<div class="ph">
  <div><h1><?= e($p['title']) ?> <?= e($p['full_name']) ?></h1>
    <p class="lead"><?= e($p['speciality'] ?? '') ?>
      <?= $p['license_no'] ? ' · licence ' . e($p['license_no']) : '' ?></p></div>
  <div class="actions">
    <a class="btn ghost" href="<?= url('/appointments?practitioner=' . $p['id']) ?>">Open diary</a></div>
</div>

<div class="grid g3" style="margin-bottom:16px">
  <div class="stat"><div class="k">Treatments</div><div class="v"><?= $stats['treatments'] ?></div>
    <div class="t">this month</div></div>
  <?php if (Auth::can('reports.view')): ?>
  <div class="stat"><div class="k">Revenue</div><div class="v"><?= money($stats['revenue']) ?></div>
    <div class="t"><?php if ((float) $p['commission_rate'] > 0): ?>
      commission <?= qty($p['commission_rate']) ?>% =
      <b><?= money($stats['revenue'] * (float) $p['commission_rate'] / 100) ?></b>
      <?php else: ?>this month<?php endif; ?></div></div>
  <?php endif; ?>
  <div class="stat"><div class="k">Toxin units</div><div class="v"><?= qty($stats['units']) ?>u</div>
    <div class="t">this month</div></div>
</div>

<div class="grid split">
  <div class="card">
    <div class="hd"><h3>Today</h3></div>
    <div class="tw"><table>
      <thead><tr><th>Time</th><th>Patient</th><th>Treatment</th><th>Status</th></tr></thead>
      <tbody>
      <?php if (!$today): ?><tr><td colspan="4" class="empty">Nothing booked today.</td></tr><?php endif; ?>
      <?php foreach ($today as $a): ?>
        <tr><td class="time"><?= t($a['starts_at']) ?></td>
          <td><a href="<?= url('/patients/' . $a['patient_id']) ?>"><?= e($a['patient_name']) ?></a></td>
          <td><?= e($a['treatment_name']) ?></td>
          <td><span class="pill <?= statusClass($a['status']) ?>"><?= e(label($a['status'])) ?></span></td></tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
  </div>

  <div>
    <div class="card">
      <div class="hd"><h3>Working hours</h3></div>
      <div class="tw"><table><tbody>
        <?php
        $byDay = [];
        foreach ($schedule as $s) { $byDay[(int) $s['weekday']][] = $s; }
        foreach ($days as $i => $dayName): ?>
          <tr>
            <td><?= e($dayName) ?></td>
            <td class="num">
              <?php if (empty($byDay[$i])): ?>
                <span style="color:var(--faint)">Not working</span>
              <?php else: foreach ($byDay[$i] as $s): ?>
                <?= substr($s['start_time'], 0, 5) ?>–<?= substr($s['end_time'], 0, 5) ?>
              <?php endforeach; endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>

    <div class="card">
      <div class="hd"><h3>Upcoming leave</h3></div>
      <div class="tw"><table><tbody>
        <?php if (!$timeOff): ?><tr><td class="empty">No leave booked.</td></tr><?php endif; ?>
        <?php foreach ($timeOff as $o): ?>
          <tr><td><?= d($o['starts_at']) ?> – <?= d($o['ends_at']) ?>
            <small style="display:block;color:var(--faint)"><?= e($o['reason']) ?></small></td></tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>
  </div>
</div>
