<?php use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>New product</h1><p class="lead">A product is the thing; batches are the vials you actually hold</p></div>
  <div class="actions"><a class="btn ghost" href="<?= url('/stock') ?>">Cancel</a></div>
</div>

<form method="post" action="<?= url('/stock/products') ?>">
  <?= Csrf::field() ?>
  <div class="card" style="max-width:620px"><div class="bd">
    <div class="row2">
      <div class="field"><label for="name">Name</label>
        <input type="text" id="name" name="name" required placeholder="e.g. Botulinum Toxin 100u"></div>
      <div class="field"><label for="brand">Brand</label>
        <input type="text" id="brand" name="brand" placeholder="e.g. Botox® Allergan"></div>
    </div>
    <div class="row3">
      <div class="field"><label for="type">Type</label>
        <select id="type" name="type">
          <?php foreach (['toxin','filler','thread','skinbooster','consumable'] as $t): ?>
            <option value="<?= $t ?>"><?= label($t) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="field"><label for="unit">Measured in</label>
        <select id="unit" name="unit">
          <option value="unit">Units</option><option value="ml">Millilitres</option>
          <option value="piece">Pieces</option></select></div>
      <div class="field"><label for="unit_cost">Cost per unit</label>
        <input type="number" id="unit_cost" name="unit_cost" step="0.01" min="0" value="0"></div>
    </div>
    <div class="field"><label for="reorder_level">Alert when stock falls to</label>
      <input type="number" id="reorder_level" name="reorder_level" step="0.1" min="0" value="0">
      <p class="hint">The dashboard raises a low-stock alert at or below this number.</p></div>
    <button class="btn" type="submit">Add product</button>
  </div></div>
</form>
