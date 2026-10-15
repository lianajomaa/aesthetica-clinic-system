<?php use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>Add a staff member</h1></div>
  <div class="actions"><a class="btn ghost" href="<?= url('/staff') ?>">Cancel</a></div>
</div>

<form method="post" action="<?= url('/staff') ?>">
  <?= Csrf::field() ?>
  <div class="grid g2">
    <div class="card"><div class="bd">
      <fieldset><legend>Account</legend>
        <div class="field"><label for="full_name">Full name</label>
          <input type="text" id="full_name" name="full_name" required value="<?= old('full_name') ?>"></div>
        <div class="field"><label for="email">Email</label>
          <input type="email" id="email" name="email" required value="<?= old('email') ?>"></div>
        <div class="row2">
          <div class="field"><label for="password">Password</label>
            <input type="password" id="password" name="password" required minlength="8"></div>
          <div class="field"><label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" value="<?= old('phone') ?>"></div>
        </div>
        <div class="field"><label for="role_id">Role</label>
          <select id="role_id" name="role_id" required>
            <?php foreach ($roles as $r): ?>
              <option value="<?= (int) $r['id'] ?>"><?= e($r['name']) ?> — <?= e($r['description']) ?></option>
            <?php endforeach; ?>
          </select></div>
      </fieldset>
    </div></div>

    <div class="card"><div class="bd">
      <fieldset><legend>Practitioner profile</legend>
        <label class="check"><input type="checkbox" name="is_practitioner" id="isprac">
          This person performs treatments and needs a diary</label>

        <div id="pracfields" style="display:none;margin-top:12px">
          <div class="row2">
            <div class="field"><label for="title">Title</label>
              <input type="text" id="title" name="title" value="Dr."></div>
            <div class="field"><label for="license_no">Licence number</label>
              <input type="text" id="license_no" name="license_no"></div>
          </div>
          <div class="field"><label for="speciality">Speciality</label>
            <input type="text" id="speciality" name="speciality" placeholder="e.g. Dermatology · fillers"></div>
          <div class="row2">
            <div class="field"><label for="commission_rate">Commission %</label>
              <input type="number" id="commission_rate" name="commission_rate" step="0.5" min="0" value="0"></div>
            <div class="field"><label for="calendar_colour">Diary colour</label>
              <input type="color" id="calendar_colour" name="calendar_colour" value="#1F6F52"
                     style="height:38px;padding:3px"></div>
          </div>
          <p class="hint">Monday to Friday, 09:00–18:00 is set up by default. You can change it afterwards.</p>
        </div>
      </fieldset>
      <button class="btn" type="submit">Create account</button>
    </div></div>
  </div>
</form>

<script>
  var cb = document.getElementById('isprac'), box = document.getElementById('pracfields');
  cb.addEventListener('change', function () { box.style.display = cb.checked ? 'block' : 'none'; });
</script>
