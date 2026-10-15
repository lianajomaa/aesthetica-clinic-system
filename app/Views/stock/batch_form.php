<?php use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>Receive stock</h1>
    <p class="lead">Record the lot number and expiry exactly as printed on the box</p></div>
  <div class="actions"><a class="btn ghost" href="<?= url('/stock') ?>">Cancel</a></div>
</div>

<form method="post" action="<?= url('/stock/batches') ?>">
  <?= Csrf::field() ?>
  <div class="card" style="max-width:620px"><div class="bd">
    <div class="field"><label for="product_id">Product</label>
      <select id="product_id" name="product_id" required>
        <?php foreach ($products as $p): ?>
          <option value="<?= (int) $p['id'] ?>"><?= e($p['name']) ?>
            <?= $p['brand'] ? '(' . e($p['brand']) . ')' : '' ?> — in <?= e($p['unit']) ?>s</option>
        <?php endforeach; ?>
      </select></div>

    <div class="row2">
      <div class="field"><label for="lot_number">Lot number</label>
        <input type="text" id="lot_number" name="lot_number" required placeholder="e.g. BX-4471-A"></div>
      <div class="field"><label for="expiry_date">Expiry date</label>
        <input type="date" id="expiry_date" name="expiry_date" required></div>
    </div>

    <div class="row2">
      <div class="field"><label for="qty_received">Quantity received</label>
        <input type="number" id="qty_received" name="qty_received" step="0.1" min="0" required></div>
      <div class="field"><label for="received_at">Received on</label>
        <input type="date" id="received_at" name="received_at" value="<?= date('Y-m-d') ?>"></div>
    </div>

    <div class="field"><label for="supplier_id">Supplier</label>
      <select id="supplier_id" name="supplier_id">
        <option value="">—</option>
        <?php foreach ($suppliers as $s): ?>
          <option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select></div>

    <button class="btn" type="submit">Add to stock</button>
  </div></div>
</form>
