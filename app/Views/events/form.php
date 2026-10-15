<?php use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>Record an adverse event</h1>
    <p class="lead">Linking it to the treatment also links it to the batch that was used</p></div>
  <div class="actions"><a class="btn ghost" href="<?= url('/events') ?>">Cancel</a></div>
</div>

<form method="post" action="<?= url('/events') ?>">
  <?= Csrf::field() ?>
  <div class="card"><div class="bd">
    <div class="row2">
      <div class="field"><label for="patient_id">Patient</label>
        <select id="patient_id" name="patient_id" required>
          <option value="">Choose…</option>
          <?php foreach ($patients as $p): ?>
            <option value="<?= (int) $p['id'] ?>" <?= $prefill['patient_id'] === (int) $p['id'] ? 'selected' : '' ?>>
              <?= e($p['full_name']) ?> — <?= e($p['code']) ?></option>
          <?php endforeach; ?>
        </select></div>

      <div class="field"><label for="treatment_record_id">Related treatment</label>
        <select id="treatment_record_id" name="treatment_record_id">
          <option value="">Not linked to a specific treatment</option>
          <?php foreach ($records as $r): ?>
            <option value="<?= (int) $r['id'] ?>" data-patient="<?= (int) $r['patient_id'] ?>">
              <?= d($r['performed_at']) ?> — <?= e($r['treatment_name']) ?></option>
          <?php endforeach; ?>
        </select></div>
    </div>

    <div class="row2">
      <div class="field"><label for="type">What happened</label>
        <input type="text" id="type" name="type" required list="evtypes"
               placeholder="e.g. Bruising, nodule, swelling"></div>
      <div class="field"><label for="severity">Severity</label>
        <select id="severity" name="severity">
          <option value="mild">Mild</option><option value="moderate">Moderate</option>
          <option value="severe">Severe</option></select></div>
    </div>

    <div class="field"><label for="description">Description</label>
      <textarea id="description" name="description" placeholder="When it appeared, how it presented"></textarea></div>
    <div class="field"><label for="action_taken">Action taken</label>
      <textarea id="action_taken" name="action_taken" placeholder="Advice given, treatment, review arranged"></textarea></div>

    <label class="check"><input type="checkbox" name="resolved"> Already resolved</label>
    <button class="btn" type="submit">Record event</button>
  </div></div>
</form>

<datalist id="evtypes">
  <?php foreach (['Bruising','Swelling','Nodule','Asymmetry','Ptosis','Headache','Infection',
                  'Allergic reaction','Vascular occlusion','Tyndall effect','Granuloma'] as $t): ?>
    <option value="<?= e($t) ?>">
  <?php endforeach; ?>
</datalist>
