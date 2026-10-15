<?php use App\Core\Auth; ?>

<div class="ph">
  <div>
    <h1>Diary</h1>
    <p class="lead"><?= date('l j F Y', strtotime($date)) ?> ·
      <?= count($appointments) ?> appointment<?= count($appointments) === 1 ? '' : 's' ?></p>
  </div>
  <div class="actions">
    <a class="btn ghost" href="<?= url('/appointments?date=' . date('Y-m-d', strtotime($date . ' -1 day')) . '&practitioner=' . $selectedPrac) ?>">←</a>
    <a class="btn ghost" href="<?= url('/appointments?date=' . date('Y-m-d')) ?>">Today</a>
    <a class="btn ghost" href="<?= url('/appointments?date=' . date('Y-m-d', strtotime($date . ' +1 day')) . '&practitioner=' . $selectedPrac) ?>">→</a>
    <?php if (Auth::can('appointments.manage')): ?>
      <a class="btn" href="<?= url('/appointments/create?date=' . e($date)) ?>">+ Book</a>
    <?php endif; ?>
  </div>
</div>

<form method="get" action="<?= url('/appointments') ?>" class="card" style="margin-bottom:16px">
  <div class="bd" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
    <div class="field" style="margin:0"><label for="date">Date</label>
      <input type="date" id="date" name="date" value="<?= e($date) ?>"></div>
    <div class="field" style="margin:0;min-width:200px"><label for="practitioner">Practitioner</label>
      <select id="practitioner" name="practitioner">
        <option value="0">Everyone</option>
        <?php foreach ($practitioners as $pr): ?>
          <option value="<?= (int) $pr['id'] ?>" <?= $selectedPrac === (int) $pr['id'] ? 'selected' : '' ?>>
            <?= e($pr['full_name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <button class="btn ghost" type="submit">Show</button>
  </div>
</form>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>Time</th><th>Patient</th><th>Treatment</th><th>Practitioner</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$appointments): ?>
      <tr><td colspan="6" class="empty">Nothing booked for this day.</td></tr>
    <?php endif; ?>
    <?php foreach ($appointments as $a): ?>
      <tr>
        <td class="time"><?= t($a['starts_at']) ?><br>
          <small style="color:var(--faint);font-weight:400"><?= (int) $a['duration_min'] ?> min</small></td>
        <td><a class="who" href="<?= url('/patients/' . $a['patient_id']) ?>">
          <span class="av"><?= e(initials($a['patient_name'])) ?></span>
          <span><span class="nm"><?= e($a['patient_name']) ?></span><small><?= e($a['phone']) ?></small></span>
        </a></td>
        <td><?= e($a['treatment_name']) ?>
          <small style="display:block;color:var(--faint)"><?= e(label($a['category'])) ?></small></td>
        <td style="white-space:nowrap"><span class="dot" style="background:<?= e($a['calendar_colour']) ?>"></span>
          <?= e($a['practitioner_name']) ?></td>
        <td><span class="pill <?= statusClass($a['status']) ?>"><?= e(label($a['status'])) ?></span></td>
        <td class="num"><a class="btn ghost sm" href="<?= url('/appointments/' . $a['id']) ?>">Open</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
