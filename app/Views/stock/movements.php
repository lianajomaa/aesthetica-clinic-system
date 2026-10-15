<div class="ph">
  <div><h1>Stock movements</h1><p class="lead">Everything in, out and wasted</p></div>
  <div class="actions"><a class="btn ghost" href="<?= url('/stock') ?>">Back to stock</a></div>
</div>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>When</th><th>Product</th><th>Lot</th><th>Type</th><th class="num">Quantity</th>
      <th>Reason</th><th>By</th></tr></thead>
    <tbody>
    <?php if (!$movements): ?><tr><td colspan="7" class="empty">No movements yet.</td></tr><?php endif; ?>
    <?php foreach ($movements as $m): ?>
      <tr>
        <td class="time"><?= dt($m['created_at'], 'j M y') ?>
          <small style="display:block;color:var(--faint);font-weight:400"><?= t($m['created_at']) ?></small></td>
        <td><?= e($m['name']) ?><small style="display:block;color:var(--faint)"><?= e($m['brand']) ?></small></td>
        <td><code><?= e($m['lot_number']) ?></code></td>
        <td><span class="pill <?= $m['type'] === 'in' ? 'ok' : ($m['type'] === 'waste' ? 'bad' : 'muted') ?>">
          <?= e(label($m['type'])) ?></span></td>
        <td class="num"><?= $m['type'] === 'in' ? '+' : '−' ?><?= qty($m['quantity']) ?> <?= e($m['unit']) ?></td>
        <td><?= e($m['note']) ?>
          <?php if ($m['reference_type'] === 'treatment_record' && $m['reference_id']): ?>
            <a href="<?= url('/records/' . $m['reference_id']) ?>">#<?= (int) $m['reference_id'] ?></a>
          <?php endif; ?></td>
        <td><?= e($m['user_name'] ?? '—') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
