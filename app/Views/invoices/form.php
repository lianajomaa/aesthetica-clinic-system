<?php
use App\Core\Csrf;
$suggest = null;
if ($appt) {
    $units = (float) ($appt['units'] ?? 0);
    $suggest = [
        'desc'  => $appt['treatment_name'] . ($appt['price_type'] === 'per_unit' && $units ? " — {$units} units" : ''),
        'qty'   => $appt['price_type'] === 'per_unit' && $units ? $units : 1,
        'price' => (float) $appt['price'],
    ];
}
?>

<div class="ph">
  <div><h1>New invoice</h1>
    <?php if ($appt): ?><p class="lead">For <?= e($appt['patient_name']) ?> — <?= e($appt['treatment_name']) ?></p><?php endif; ?></div>
  <div class="actions"><a class="btn ghost" href="<?= url('/invoices') ?>">Cancel</a></div>
</div>

<form method="post" action="<?= url('/invoices') ?>">
  <?= Csrf::field() ?>
  <?php if ($appt): ?><input type="hidden" name="appointment_id" value="<?= (int) $appt['id'] ?>"><?php endif; ?>

  <div class="card"><div class="bd">
    <div class="field" style="max-width:420px"><label for="patient_id">Patient</label>
      <select id="patient_id" name="patient_id" required>
        <option value="">Choose…</option>
        <?php foreach ($patients as $p): ?>
          <option value="<?= (int) $p['id'] ?>"
            <?= ($appt && (int) $appt['patient_id'] === (int) $p['id']) ? 'selected' : '' ?>>
            <?= e($p['full_name']) ?> — <?= e($p['code']) ?></option>
        <?php endforeach; ?>
      </select></div>
  </div></div>

  <div class="card">
    <div class="hd"><h3>Lines</h3><button class="btn ghost sm" type="button" id="addline">+ Add line</button></div>
    <div class="bd">
      <div class="site" style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;
           color:var(--faint);font-weight:700;margin-bottom:6px">
        <span>Description</span><span>Quantity</span><span>Unit price</span><span></span></div>
      <div class="sitelist" id="lines"></div>
      <div class="field" style="max-width:220px;margin-top:16px"><label for="discount">Discount</label>
        <input type="number" id="discount" name="discount" step="0.01" min="0" value="0"></div>
      <button class="btn" type="submit">Create invoice</button>
    </div>
  </div>
</form>

<template id="linetpl">
  <div class="site" style="grid-template-columns:2fr .7fr .9fr auto">
    <input type="text" name="description[]" placeholder="e.g. Botox — upper face, 28 units">
    <input type="number" name="quantity[]" step="0.1" min="0" value="1">
    <input type="number" name="unit_price[]" step="0.01" min="0" value="0">
    <button type="button" class="btn ghost sm rm">Remove</button>
  </div>
</template>

<script>
  var tpl = document.getElementById('linetpl'), list = document.getElementById('lines');
  function addLine(desc, qty, price) {
    var node = tpl.content.cloneNode(true);
    if (desc) {
      node.querySelector('[name="description[]"]').value = desc;
      node.querySelector('[name="quantity[]"]').value = qty;
      node.querySelector('[name="unit_price[]"]').value = price;
    }
    list.appendChild(node);
  }
  document.getElementById('addline').addEventListener('click', function () { addLine(); });
  list.addEventListener('click', function (ev) {
    if (ev.target.classList.contains('rm')) ev.target.closest('.site').remove();
  });
  <?php if ($suggest): ?>
    addLine(<?= json_encode($suggest['desc']) ?>, <?= (float) $suggest['qty'] ?>, <?= (float) $suggest['price'] ?>);
  <?php else: ?>
    addLine();
  <?php endif; ?>
  addLine();
</script>
