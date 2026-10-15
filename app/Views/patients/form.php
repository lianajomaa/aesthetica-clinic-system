<?php
use App\Core\Csrf;
$p = $patient;
$action = $p ? url('/patients/' . $p['id']) : url('/patients');
?>

<div class="ph">
  <div><h1><?= $p ? 'Edit patient' : 'New patient' ?></h1>
    <?php if ($p): ?><p class="lead"><?= e($p['code']) ?></p><?php endif; ?></div>
  <div class="actions"><a class="btn ghost" href="<?= url($p ? '/patients/' . $p['id'] : '/patients') ?>">Cancel</a></div>
</div>

<form method="post" action="<?= $action ?>">
  <?= Csrf::field() ?>
  <div class="grid g2">
    <div class="card"><div class="bd">
      <fieldset><legend>Details</legend>
        <div class="field"><label for="full_name">Full name</label>
          <input type="text" id="full_name" name="full_name" required value="<?= old('full_name', $p['full_name'] ?? '') ?>"></div>
        <div class="row2">
          <div class="field"><label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" required value="<?= old('phone', $p['phone'] ?? '') ?>"></div>
          <div class="field"><label for="date_of_birth">Date of birth</label>
            <input type="date" id="date_of_birth" name="date_of_birth" value="<?= old('date_of_birth', $p['date_of_birth'] ?? '') ?>"></div>
        </div>
        <div class="row2">
          <div class="field"><label for="gender">Gender</label>
            <select id="gender" name="gender">
              <?php foreach (['' => '—', 'female' => 'Female', 'male' => 'Male', 'other' => 'Other'] as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($p['gender'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="field"><label for="source">How did they find us</label>
            <select id="source" name="source">
              <?php foreach (['instagram','referral','walk_in','google','facebook','other'] as $s): ?>
                <option value="<?= $s ?>" <?= ($p['source'] ?? '') === $s ? 'selected' : '' ?>><?= label($s) ?></option>
              <?php endforeach; ?>
            </select></div>
        </div>
        <div class="field"><label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?= old('email', $p['email'] ?? '') ?>"></div>
        <div class="field"><label for="address">Address</label>
          <input type="text" id="address" name="address" value="<?= old('address', $p['address'] ?? '') ?>"></div>
      </fieldset>
    </div></div>

    <div class="card"><div class="bd">
      <fieldset><legend>Notes</legend>
        <div class="field"><label for="notes">Anything the team should know</label>
          <textarea id="notes" name="notes" style="min-height:220px"><?= e($p['notes'] ?? '') ?></textarea>
          <p class="hint">Preferences, sensitivities, how they like to be contacted. Clinical history is recorded separately on the patient page.</p>
        </div>
      </fieldset>
      <button class="btn" type="submit"><?= $p ? 'Save changes' : 'Create patient' ?></button>
    </div></div>
  </div>
</form>
