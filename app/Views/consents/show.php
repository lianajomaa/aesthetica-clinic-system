<?php $c = $consent; ?>

<div class="ph">
  <div><h1>Consent form</h1>
    <p class="lead"><?= e($c['treatment_name']) ?> · <?= e($c['patient_name']) ?> (<?= e($c['code']) ?>)</p></div>
  <div class="actions noprint">
    <button class="btn ghost" onclick="window.print()">Print</button>
    <a class="btn ghost" href="<?= url('/patients/' . $c['patient_id']) ?>">Patient record</a>
  </div>
</div>

<div class="card">
  <div class="hd"><div><h3><?= e(setting('clinic_name', 'Aesthetica')) ?> — treatment consent</h3>
    <div class="sub">Version <?= e($c['version']) ?></div></div></div>
  <div class="bd">
    <div class="meta" style="margin-bottom:22px">
      <div><div class="k">Patient</div><div class="v"><?= e($c['patient_name']) ?></div></div>
      <div><div class="k">Date of birth</div><div class="v"><?= d($c['date_of_birth']) ?></div></div>
      <div><div class="k">Treatment</div><div class="v"><?= e($c['treatment_name']) ?></div></div>
      <div><div class="k">Signed</div><div class="v"><?= dt($c['signed_at']) ?></div></div>
    </div>

    <div style="white-space:pre-line;line-height:1.8;font-size:14px;border-top:1px solid var(--line);
                border-bottom:1px solid var(--line);padding:20px 0"><?= e($c['body_snapshot']) ?></div>

    <div class="meta" style="margin-top:22px">
      <div><div class="k">Signed by</div>
        <div class="v" style="font-size:19px;font-family:var(--display)"><?= e($c['signed_name']) ?></div></div>
      <div><div class="k">Witnessed by</div><div class="v"><?= e($c['witness_name'] ?? '—') ?></div></div>
      <div><div class="k">Photography for marketing</div>
        <div class="v"><?= $c['photo_consent'] ? 'Agreed' : 'Not agreed' ?></div></div>
      <div><div class="k">Recorded from</div><div class="v"><?= e($c['ip_address'] ?? '—') ?></div></div>
    </div>
  </div>
</div>
