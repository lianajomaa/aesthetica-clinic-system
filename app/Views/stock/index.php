<?php use App\Core\Auth; use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>Stock &amp; vials</h1>
    <p class="lead">Tracked by batch, so every dose can be traced back to a lot number</p></div>
  <?php if (Auth::can('stock.manage')): ?>
  <div class="actions">
    <a class="btn ghost" href="<?= url('/stock/movements') ?>">Movements</a>
    <a class="btn ghost" href="<?= url('/stock/products/create') ?>">+ Product</a>
    <a class="btn" href="<?= url('/stock/batches/create') ?>">+ Receive stock</a>
  </div>
  <?php endif; ?>
</div>

<div class="card">
  <div class="hd"><h3>Products</h3></div>
  <div class="tw">
    <table>
      <thead><tr><th>Product</th><th>Type</th><th class="num">In stock</th><th>Level</th>
        <th>Next expiry</th><th class="num">Cost</th></tr></thead>
      <tbody>
      <?php foreach ($products as $p):
        $stock = (float) $p['in_stock'];
        $reorder = (float) $p['reorder_level'];
        $pct = $reorder > 0 ? min(100, ($stock / max($reorder * 3, 1)) * 100) : ($stock > 0 ? 100 : 0);
        $cls = $stock <= 0 ? 'out' : ($stock <= $reorder ? 'low' : ''); ?>
        <tr>
          <td><strong><?= e($p['name']) ?></strong>
            <small style="display:block;color:var(--faint)"><?= e($p['brand']) ?></small></td>
          <td><span class="pill muted"><?= e(label($p['type'])) ?></span></td>
          <td class="num"><strong><?= qty($stock) ?></strong> <?= e($p['unit']) ?>
            <?php if ((float) $p['expired'] > 0): ?>
              <small style="display:block;color:var(--bad)"><?= qty($p['expired']) ?> expired</small>
            <?php endif; ?></td>
          <td style="min-width:130px">
            <div class="bar"><i class="<?= $cls ?>" style="width:<?= round($pct) ?>%"></i></div>
            <small style="color:var(--faint)">reorder at <?= qty($reorder) ?></small></td>
          <td><?= $p['next_expiry'] ? d($p['next_expiry'], 'M Y') : '—' ?></td>
          <td class="num"><?= money($p['unit_cost']) ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="hd"><div><h3>Batches</h3><div class="sub">Oldest expiry first — use these up before opening new ones</div></div></div>
  <div class="tw">
    <table>
      <thead><tr><th>Lot</th><th>Product</th><th>Supplier</th><th class="num">Remaining</th>
        <th>Expiry</th><?php if (Auth::can('stock.manage')): ?><th></th><?php endif; ?></tr></thead>
      <tbody>
      <?php foreach ($batches as $b):
        $days = (int) $b['days_left']; ?>
        <tr<?= $days < 0 ? ' style="background:var(--bad-bg)"' : '' ?>>
          <td><code><?= e($b['lot_number']) ?></code></td>
          <td><?= e($b['name']) ?><small style="display:block;color:var(--faint)"><?= e($b['brand']) ?></small></td>
          <td><?= e($b['supplier_name'] ?? '—') ?></td>
          <td class="num"><?= qty($b['qty_remaining']) ?> / <?= qty($b['qty_received']) ?> <?= e($b['unit']) ?></td>
          <td><?= d($b['expiry_date'], 'j M Y') ?>
            <?php if ($days < 0): ?><span class="pill bad">Expired</span>
            <?php elseif ($days <= 45): ?><span class="pill warn"><?= $days ?> days</span><?php endif; ?></td>
          <?php if (Auth::can('stock.manage')): ?>
            <td class="num">
              <form method="post" action="<?= url('/stock/waste') ?>" style="display:inline-flex;gap:6px;align-items:center"
                    onsubmit="return confirm('Record this as wastage?')">
                <?= Csrf::field() ?>
                <input type="hidden" name="batch_id" value="<?= (int) $b['id'] ?>">
                <input type="number" name="quantity" step="0.1" min="0" placeholder="Qty" style="width:80px">
                <button class="btn ghost sm" type="submit">Waste</button>
              </form>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
