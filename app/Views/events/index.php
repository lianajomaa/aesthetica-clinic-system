<div class="ph">
  <div><h1>Adverse events</h1>
    <p class="lead">Complications recorded against the patient, the treatment and the batch</p></div>
  <div class="actions"><a class="btn" href="<?= url('/events/create') ?>">+ Record an event</a></div>
</div>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>Reported</th><th>Patient</th><th>Event</th><th>Treatment</th><th>Lot</th><th>Status</th></tr></thead>
    <tbody>
    <?php if (!$events): ?><tr><td colspan="6" class="empty">No adverse events recorded.</td></tr><?php endif; ?>
    <?php foreach ($events as $ev): ?>
      <tr>
        <td class="time"><?= d($ev['reported_at'], 'j M y') ?>
          <small style="display:block;color:var(--faint);font-weight:400"><?= e($ev['reporter'] ?? '') ?></small></td>
        <td><a href="<?= url('/patients/' . $ev['patient_id']) ?>"><?= e($ev['patient_name']) ?></a>
          <small style="display:block;color:var(--faint)"><?= e($ev['code']) ?></small></td>
        <td><strong><?= e($ev['type']) ?></strong>
          <span class="pill <?= statusClass($ev['severity']) ?>"><?= e($ev['severity']) ?></span>
          <div style="font-size:12.5px;color:var(--muted);margin-top:4px"><?= e($ev['description']) ?></div>
          <?php if ($ev['action_taken']): ?>
            <div style="font-size:12.5px;color:var(--muted);margin-top:4px">
              <strong>Action:</strong> <?= e($ev['action_taken']) ?></div>
          <?php endif; ?></td>
        <td><?= e($ev['treatment_name'] ?? '—') ?></td>
        <td><?= $ev['lot_number'] ? '<code>' . e($ev['lot_number']) . '</code>' : '—' ?></td>
        <td><span class="pill <?= $ev['resolved'] ? 'ok' : 'warn' ?>">
          <?= $ev['resolved'] ? 'Resolved' : 'Open' ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
