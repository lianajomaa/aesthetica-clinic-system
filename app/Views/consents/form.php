<?php use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>Consent form</h1>
    <p class="lead">Read through with the patient, then record their agreement</p></div>
  <div class="actions"><a class="btn ghost" href="<?= url('/consents') ?>">Cancel</a></div>
</div>

<form method="post" action="<?= url('/consents') ?>">
  <?= Csrf::field() ?>
  <input type="hidden" name="appointment_id" value="<?= (int) $prefill['appointment_id'] ?>">

  <div class="grid split">
    <div class="card">
      <div class="hd"><h3>What the patient is agreeing to</h3></div>
      <div class="bd">
        <div style="white-space:pre-line;color:var(--muted);font-size:13.5px;line-height:1.75">
          <?= e($body) ?>
        </div>
        <p class="hint" style="margin-top:14px">The wording above is stored with the signature, so the
          record always shows exactly what was agreed on the day.</p>
      </div>
    </div>

    <div class="card">
      <div class="hd"><h3>Record the agreement</h3></div>
      <div class="bd">
        <div class="field"><label for="patient_id">Patient</label>
          <select id="patient_id" name="patient_id" required>
            <option value="">Choose…</option>
            <?php foreach ($patients as $p): ?>
              <option value="<?= (int) $p['id'] ?>" <?= $prefill['patient_id'] === (int) $p['id'] ? 'selected' : '' ?>>
                <?= e($p['full_name']) ?> — <?= e($p['code']) ?></option>
            <?php endforeach; ?>
          </select></div>

        <div class="field"><label for="treatment_id">Treatment</label>
          <select id="treatment_id" name="treatment_id" required>
            <?php foreach ($treatments as $t): ?>
              <option value="<?= (int) $t['id'] ?>" <?= $prefill['treatment_id'] === (int) $t['id'] ? 'selected' : '' ?>>
                <?= e($t['name']) ?></option>
            <?php endforeach; ?>
          </select></div>

        <div class="field"><label for="signed_name">Patient’s full name, typed by them</label>
          <input type="text" id="signed_name" name="signed_name" required placeholder="As it appears on their ID"></div>

        <label class="check"><input type="checkbox" name="agree" required>
          The patient confirms they have read and understood the above, and consents to treatment.</label>

        <label class="check"><input type="checkbox" name="photo_consent">
          Separately, the patient agrees that before and after photographs may be used in marketing.</label>

        <button class="btn" type="submit" style="margin-top:10px">Store consent</button>
      </div>
    </div>
  </div>
</form>
