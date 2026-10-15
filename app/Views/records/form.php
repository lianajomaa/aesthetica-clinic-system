<?php use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>Record a treatment</h1>
    <p class="lead">Each row is one injection site. Stock comes off the exact batch you choose.</p></div>
  <div class="actions"><a class="btn ghost" href="<?= url('/records') ?>">Cancel</a></div>
</div>

<form method="post" action="<?= url('/records') ?>">
  <?= Csrf::field() ?>
  <?php if ($appt): ?>
    <input type="hidden" name="appointment_id" value="<?= (int) $appt['id'] ?>">
  <?php endif; ?>

  <div class="card">
    <div class="hd"><h3>Visit</h3></div>
    <div class="bd">
      <div class="row3">
        <div class="field"><label for="patient_id">Patient</label>
          <select id="patient_id" name="patient_id" required>
            <option value="">Choose…</option>
            <?php foreach ($patients as $p): ?>
              <option value="<?= (int) $p['id'] ?>"
                <?= ($appt && (int) $appt['patient_id'] === (int) $p['id']) ? 'selected' : '' ?>>
                <?= e($p['full_name']) ?> — <?= e($p['code']) ?></option>
            <?php endforeach; ?>
          </select></div>

        <div class="field"><label for="treatment_id">Treatment</label>
          <select id="treatment_id" name="treatment_id" required>
            <?php foreach ($treatments as $t): ?>
              <option value="<?= (int) $t['id'] ?>"
                <?= ($appt && (int) $appt['treatment_id'] === (int) $t['id']) ? 'selected' : '' ?>>
                <?= e($t['name']) ?></option>
            <?php endforeach; ?>
          </select></div>

        <div class="field"><label for="practitioner_id">Performed by</label>
          <select id="practitioner_id" name="practitioner_id" required>
            <?php foreach ($practitioners as $pr): ?>
              <option value="<?= (int) $pr['id'] ?>"
                <?= ($appt && (int) $appt['practitioner_id'] === (int) $pr['id']) ? 'selected' : '' ?>>
                <?= e($pr['full_name']) ?></option>
            <?php endforeach; ?>
          </select></div>
      </div>

      <div class="row2">
        <div class="field"><label for="performed_at">Performed at</label>
          <input type="datetime-local" id="performed_at" name="performed_at"
                 value="<?= date('Y-m-d\TH:i', $appt ? strtotime($appt['starts_at']) : time()) ?>"></div>
        <div class="field"><label for="technique">Technique</label>
          <input type="text" id="technique" name="technique"
                 placeholder="e.g. 30G needle, 2.5ml saline dilution"></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="hd"><div><h3>Injection sites &amp; doses</h3>
      <div class="sub">Choose the batch actually used — this is what makes the record traceable</div></div>
      <button class="btn ghost sm" type="button" id="addrow">+ Add site</button></div>
    <div class="bd">
      <div class="site" style="font-size:11px;letter-spacing:.1em;text-transform:uppercase;
           color:var(--faint);font-weight:700;margin-bottom:6px">
        <span>Site</span><span>Product &amp; batch</span><span>Quantity</span><span></span>
      </div>
      <div class="sitelist" id="sites"></div>
      <p class="hint">Only batches that are in stock and in date are listed.</p>
    </div>
  </div>

  <div class="card">
    <div class="bd">
      <div class="field"><label for="notes">Clinical notes</label>
        <textarea id="notes" name="notes" placeholder="Observations, patient response, anything relevant for next time"></textarea></div>
      <button class="btn" type="submit">Save record &amp; deduct stock</button>
    </div>
  </div>
</form>

<template id="rowtpl">
  <div class="site">
    <input type="text" name="site[]" placeholder="e.g. Glabella" list="sitelist">
    <select name="batch_id[]">
      <option value="">Choose a batch…</option>
      <?php foreach ($batches as $b): ?>
        <option value="<?= (int) $b['id'] ?>">
          <?= e($b['name']) ?><?= $b['brand'] ? ' (' . e($b['brand']) . ')' : '' ?>
          — lot <?= e($b['lot_number']) ?> · <?= qty($b['qty_remaining']) ?> <?= e($b['unit']) ?> left
          · exp <?= d($b['expiry_date'], 'M y') ?>
        </option>
      <?php endforeach; ?>
    </select>
    <input type="number" name="quantity[]" step="0.1" min="0" placeholder="Qty">
    <button type="button" class="btn ghost sm rm">Remove</button>
  </div>
</template>

<datalist id="sitelist">
  <?php foreach (['Glabella','Forehead — central','Forehead — lateral','Left frontalis','Right frontalis',
                  'Left orbicularis','Right orbicularis','Left masseter','Right masseter','Upper lip — body',
                  'Lower lip — body','Left malar','Right malar','Left nasolabial','Right nasolabial',
                  'Left jawline','Right jawline','Chin','Neck'] as $s): ?>
    <option value="<?= e($s) ?>">
  <?php endforeach; ?>
</datalist>

<script>
  var tpl = document.getElementById('rowtpl'), list = document.getElementById('sites');
  function addRow() { list.appendChild(tpl.content.cloneNode(true)); }
  document.getElementById('addrow').addEventListener('click', addRow);
  list.addEventListener('click', function (ev) {
    if (ev.target.classList.contains('rm')) { ev.target.closest('.site').remove(); }
  });
  addRow(); addRow();
</script>
