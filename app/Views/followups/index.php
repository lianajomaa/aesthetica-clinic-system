<?php use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>Follow-ups &amp; recalls</h1>
    <p class="lead">Two-week reviews, top-ups and patients due a repeat treatment</p></div>
</div>

<div class="tabs">
  <a class="<?= $filter === 'pending' ? 'on' : '' ?>" href="<?= url('/followups?filter=pending') ?>">
    Pending (<?= $counts['pending'] ?>)</a>
  <a class="<?= $filter === 'overdue' ? 'on' : '' ?>" href="<?= url('/followups?filter=overdue') ?>">
    Overdue (<?= $counts['overdue'] ?>)</a>
  <a class="<?= $filter === 'recall' ? 'on' : '' ?>" href="<?= url('/followups?filter=recall') ?>">
    Recalls (<?= $counts['recall'] ?>)</a>
  <a class="<?= $filter === 'done' ? 'on' : '' ?>" href="<?= url('/followups?filter=done') ?>">Completed</a>
</div>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>Due</th><th>Patient</th><th>Type</th><th>After</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$items): ?><tr><td colspan="6" class="empty">Nothing here.</td></tr><?php endif; ?>
    <?php foreach ($items as $f):
      $overdue = $f['status'] === 'pending' && $f['due_date'] < date('Y-m-d'); ?>
      <tr>
        <td class="time"><?= d($f['due_date'], 'j M y') ?>
          <?php if ($overdue): ?><br><span class="pill bad">Overdue</span><?php endif; ?></td>
        <td><a class="who" href="<?= url('/patients/' . $f['patient_id']) ?>">
          <span class="av"><?= e(initials($f['patient_name'])) ?></span>
          <span><span class="nm"><?= e($f['patient_name']) ?></span><small><?= e($f['phone']) ?></small></span></a></td>
        <td><span class="pill <?= $f['type'] === 'recall' ? 'info' : 'muted' ?>"><?= e(label($f['type'])) ?></span>
          <small style="display:block;color:var(--faint);margin-top:3px"><?= e($f['notes']) ?></small></td>
        <td><?= e($f['treatment_name'] ?? '—') ?></td>
        <td><span class="pill <?= statusClass($f['status']) ?>"><?= e(label($f['status'])) ?></span></td>
        <td class="num">
          <?php if ($f['status'] === 'pending'): ?>
            <form method="post" action="<?= url('/followups/' . $f['id'] . '/done') ?>" style="display:inline-flex;gap:6px">
              <?= Csrf::field() ?>
              <input type="hidden" name="filter" value="<?= e($filter) ?>">
              <button class="btn sm" name="action" value="done" type="submit">Done</button>
              <button class="btn ghost sm" name="action" value="dismiss" type="submit">Dismiss</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
