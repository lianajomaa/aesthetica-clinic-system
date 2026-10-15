<?php use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>New appointment</h1>
    <p class="lead">The slot is checked against working hours, leave and existing bookings.</p></div>
  <div class="actions"><a class="btn ghost" href="<?= url('/appointments') ?>">Cancel</a></div>
</div>

<form method="post" action="<?= url('/appointments') ?>">
  <?= Csrf::field() ?>
  <div class="grid g2">
    <div class="card"><div class="bd">
      <fieldset><legend>Who</legend>
        <div class="field"><label for="patient_id">Patient</label>
          <select id="patient_id" name="patient_id" required>
            <option value="">Choose a patient…</option>
            <?php foreach ($patients as $p): ?>
              <option value="<?= (int) $p['id'] ?>" <?= $prefill['patient_id'] === (int) $p['id'] ? 'selected' : '' ?>>
                <?= e($p['full_name']) ?> — <?= e($p['code']) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="hint">Not registered yet? <a href="<?= url('/patients/create') ?>">Add the patient first</a>.</p>
        </div>
        <div class="field"><label for="practitioner_id">Practitioner</label>
          <select id="practitioner_id" name="practitioner_id" required>
            <?php foreach ($practitioners as $pr): ?>
              <option value="<?= (int) $pr['id'] ?>"><?= e($pr['full_name']) ?>
                <?= $pr['speciality'] ? '— ' . e($pr['speciality']) : '' ?></option>
            <?php endforeach; ?>
          </select></div>
      </fieldset>
    </div></div>

    <div class="card"><div class="bd">
      <fieldset><legend>What and when</legend>
        <div class="field"><label for="treatment_id">Treatment</label>
          <select id="treatment_id" name="treatment_id" required>
            <?php $cat = null; foreach ($treatments as $t):
              if ($cat !== $t['category']) { if ($cat !== null) echo '</optgroup>';
                  echo '<optgroup label="' . e(label($t['category'])) . '">'; $cat = $t['category']; } ?>
              <option value="<?= (int) $t['id'] ?>" data-min="<?= (int) $t['duration_min'] ?>">
                <?= e($t['name']) ?> · <?= (int) $t['duration_min'] ?> min
                <?= (float) $t['price'] > 0 ? '· ' . money($t['price']) . ($t['price_type'] === 'per_unit' ? '/unit' : '') : '' ?>
              </option>
            <?php endforeach; if ($cat !== null) echo '</optgroup>'; ?>
          </select>
          <p class="hint" id="dur">The end time is worked out from the treatment’s duration.</p>
        </div>
        <div class="row2">
          <div class="field"><label for="date">Date</label>
            <input type="date" id="date" name="date" required value="<?= e($prefill['date']) ?>"></div>
          <div class="field"><label for="time">Start time</label>
            <input type="time" id="time" name="time" required step="300" value="10:00"></div>
        </div>
        <div class="row2">
          <div class="field"><label for="deposit">Deposit taken</label>
            <input type="number" id="deposit" name="deposit" step="0.01" min="0" value="0"></div>
          <div class="field"><label for="notes">Note</label>
            <input type="text" id="notes" name="notes" placeholder="Optional"></div>
        </div>
        <button class="btn" type="submit">Book appointment</button>
      </fieldset>
    </div></div>
  </div>
</form>

<script>
  var sel = document.getElementById('treatment_id'), hint = document.getElementById('dur');
  function showDur() {
    var o = sel.options[sel.selectedIndex];
    hint.textContent = o ? 'This treatment takes ' + o.dataset.min + ' minutes. The slot is reserved for that long.' : '';
  }
  sel.addEventListener('change', showDur); showDur();
</script>
