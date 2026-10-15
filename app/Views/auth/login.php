<?php use App\Core\Csrf; ?>
<div class="login">
  <div class="art">
    <div>
      <div class="brand" style="border:0;padding:0">
        <div class="mark">AE</div>
        <div><b style="color:#fff">Aesthetica</b><small>Clinic Suite</small></div>
      </div>
    </div>
    <div>
      <h2>Built for injectable clinics, not for salons.</h2>
      <p>Appointments, clinical records, consent, batch-traceable stock and billing — in one place.</p>
      <ul>
        <li>Every dose recorded by site, batch and lot number</li>
        <li>Consent required before a treatment can be signed off</li>
        <li>Stock deducted from the exact vial that was used</li>
        <li>Two-week reviews and recalls raised automatically</li>
      </ul>
    </div>
    <div style="font-size:11.5px;color:#8fae9f;position:relative">
      <?= e(setting('clinic_address', 'Verdun, Beirut, Lebanon')) ?>
    </div>
  </div>

  <div class="form">
    <div class="inner">
      <h1>Sign in</h1>
      <p class="sub">Use your clinic account to continue.</p>

      <form method="post" action="<?= url('/login') ?>">
        <?= Csrf::field() ?>
        <div class="field">
          <label for="email">Email address</label>
          <input type="email" id="email" name="email" value="<?= old('email') ?>" required autofocus autocomplete="username">
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" required autocomplete="current-password">
        </div>
        <button class="btn" style="width:100%;justify-content:center" type="submit">Sign in</button>
      </form>

      <div class="demo">
        <div class="k">Demo accounts — click to fill</div>
        <button type="button" data-e="owner@aesthetica.test"><b>Dr. Rana Hajjar</b><span>Owner</span></button>
        <button type="button" data-e="karim@aesthetica.test"><b>Dr. Karim Nassar</b><span>Doctor</span></button>
        <button type="button" data-e="yara@aesthetica.test"><b>Yara Chami</b><span>Nurse</span></button>
        <button type="button" data-e="reception@aesthetica.test"><b>Nadine Rizk</b><span>Receptionist</span></button>
        <p class="hint">Password for all demo accounts: <strong>password</strong></p>
      </div>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.demo button').forEach(function (b) {
    b.addEventListener('click', function () {
      document.getElementById('email').value = b.dataset.e;
      document.getElementById('password').value = 'password';
      document.getElementById('password').focus();
    });
  });
</script>
