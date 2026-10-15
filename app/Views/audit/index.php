<div class="ph">
  <div><h1>Audit log</h1><p class="lead">Who did what, and when — clinical records need accountability</p></div>
</div>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>When</th><th>User</th><th>Action</th><th>Record</th><th>Detail</th><th>IP</th></tr></thead>
    <tbody>
    <?php if (!$entries): ?><tr><td colspan="6" class="empty">Nothing logged yet.</td></tr><?php endif; ?>
    <?php foreach ($entries as $a): ?>
      <tr>
        <td class="time"><?= dt($a['created_at'], 'j M y') ?>
          <small style="display:block;color:var(--faint);font-weight:400"><?= t($a['created_at']) ?></small></td>
        <td><?= e($a['full_name'] ?? 'System') ?>
          <small style="display:block;color:var(--faint)"><?= e($a['role_name'] ?? '') ?></small></td>
        <td><span class="pill muted"><?= e($a['action']) ?></span></td>
        <td><?= e(label($a['entity'])) ?><?= $a['entity_id'] ? ' #' . (int) $a['entity_id'] : '' ?></td>
        <td><?= e($a['detail'] ?? '') ?></td>
        <td style="color:var(--faint)"><?= e($a['ip_address'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
