<?php
use App\Core\Auth;
use App\Core\Csrf;
$p = $patient;
$age = $p['date_of_birth'] ? (new DateTime($p['date_of_birth']))->diff(new DateTime())->y : null;
?>

<div class="ph">
  <div>
    <h1><?= e($p['full_name']) ?></h1>
    <p class="lead"><?= e($p['code']) ?> · <?= e($p['phone']) ?>
      <?= $age ? ' · ' . $age . ' years' : '' ?>
      · <?= e(label($p['source'])) ?></p>
  </div>
  <div class="actions">
    <?php if (Auth::can('patients.manage')): ?>
      <a class="btn ghost" href="<?= url('/patients/' . $p['id'] . '/edit') ?>">Edit</a>
    <?php endif; ?>
    <?php if (Auth::can('appointments.manage')): ?>
      <a class="btn ghost" href="<?= url('/appointments/create?patient=' . $p['id']) ?>">Book</a>
    <?php endif; ?>
    <?php if (Auth::can('records.manage')): ?>
      <a class="btn" href="<?= url('/records/create?patient=' . $p['id']) ?>">Record treatment</a>
    <?php endif; ?>
  </div>
</div>

<div class="grid g4" style="margin-bottom:16px">
  <div class="stat"><div class="k">Visits</div><div class="v"><?= count($records) ?: count($appointments) ?></div>
    <div class="t">since <?= d($p['created_at'], 'M Y') ?></div></div>
  <?php if (Auth::can('billing.view')): ?>
  <div class="stat"><div class="k">Lifetime spend</div><div class="v"><?= money($spend) ?></div>
    <div class="t">across <?= count($invoices) ?> invoices</div></div>
  <?php endif; ?>
  <div class="stat"><div class="k">Open follow-ups</div>
    <div class="v"><?= count(array_filter($followups, fn ($f) => $f['status'] === 'pending')) ?></div>
    <div class="t">reviews and recalls</div></div>
  <div class="stat"><div class="k">Adverse events</div><div class="v"><?= count($events) ?></div>
    <div class="t"><?= count(array_filter($events, fn ($e) => !$e['resolved'])) ?> unresolved</div></div>
</div>

<div class="grid split">
  <div>
    <?php if ($clinical): ?>
    <div class="card">
      <div class="hd"><div><h3>Medical history</h3>
        <div class="sub">Reviewed <?= dt($history['reviewed_at'] ?? null) ?></div></div></div>
      <div class="bd">
        <?php
        $flags = [
            'is_pregnant' => 'Pregnant', 'is_breastfeeding' => 'Breastfeeding',
            'on_blood_thinners' => 'Blood thinners', 'has_autoimmune' => 'Autoimmune condition',
            'had_prior_reaction' => 'Prior reaction', 'smoker' => 'Smoker',
        ];
        $active = array_filter($flags, fn ($k) => !empty($history[$k]), ARRAY_FILTER_USE_KEY);
        ?>
        <?php if ($active): ?>
          <div style="margin-bottom:14px">
            <?php foreach ($active as $k => $lbl): ?>
              <span class="pill bad" style="margin-right:5px"><?= e($lbl) ?></span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <form method="post" action="<?= url('/patients/' . $p['id'] . '/history') ?>">
          <?= Csrf::field() ?>
          <div class="row3">
            <div class="field"><label>Allergies</label>
              <input type="text" name="allergies" value="<?= e($history['allergies'] ?? '') ?>"></div>
            <div class="field"><label>Medications</label>
              <input type="text" name="medications" value="<?= e($history['medications'] ?? '') ?>"></div>
            <div class="field"><label>Conditions</label>
              <input type="text" name="conditions" value="<?= e($history['conditions'] ?? '') ?>"></div>
          </div>
          <div class="field"><label>Previous aesthetic treatments</label>
            <input type="text" name="prior_treatments" value="<?= e($history['prior_treatments'] ?? '') ?>"></div>
          <div class="row3">
            <?php foreach ($flags as $k => $lbl): ?>
              <label class="check"><input type="checkbox" name="<?= $k ?>" <?= !empty($history[$k]) ? 'checked' : '' ?>>
                <?= e($lbl) ?></label>
            <?php endforeach; ?>
          </div>
          <button class="btn ghost" type="submit">Save history</button>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="hd"><h3>Treatment history</h3>
        <span class="sub"><?= count($records) ?> records</span></div>
      <div class="tw"><table>
        <thead><tr><th>Date</th><th>Treatment</th><th>Practitioner</th><th class="num">Dose</th></tr></thead>
        <tbody>
        <?php if (!$records): ?><tr><td colspan="4" class="empty">No treatments recorded yet.</td></tr><?php endif; ?>
        <?php foreach ($records as $r): ?>
          <tr>
            <td class="time"><?= d($r['performed_at']) ?></td>
            <td><a href="<?= url('/records/' . $r['id']) ?>"><?= e($r['treatment_name']) ?></a></td>
            <td><?= e($r['practitioner_name']) ?></td>
            <td class="num">
              <?= (float) $r['total_units'] > 0 ? qty($r['total_units']) . 'u' : '' ?>
              <?= (float) $r['total_ml'] > 0 ? qty($r['total_ml']) . 'ml' : '' ?>
              <?= ((float) $r['total_units'] + (float) $r['total_ml']) == 0 ? '—' : '' ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="hd"><h3>Appointments</h3></div>
      <div class="tw"><table>
        <thead><tr><th>When</th><th>Treatment</th><th>Practitioner</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (!$appointments): ?><tr><td colspan="4" class="empty">No appointments yet.</td></tr><?php endif; ?>
        <?php foreach ($appointments as $a): ?>
          <tr>
            <td class="time"><a href="<?= url('/appointments/' . $a['id']) ?>"><?= dt($a['starts_at'], 'j M Y, H:i') ?></a></td>
            <td><?= e($a['treatment_name']) ?></td>
            <td><?= e($a['practitioner_name']) ?></td>
            <td><span class="pill <?= statusClass($a['status']) ?>"><?= e(label($a['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
  </div>

  <div>
    <?php if ($clinical): ?>
    <div class="card">
      <div class="hd"><h3>Photos</h3></div>
      <div class="bd">
        <?php if ($photos): ?>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px">
            <?php foreach ($photos as $ph): ?>
              <figure style="margin:0">
                <img src="<?= url($ph['file_path']) ?>" alt="" style="width:100%;border-radius:8px;display:block">
                <figcaption style="font-size:11px;color:var(--faint);margin-top:4px">
                  <?= e(label($ph['type'])) ?> · <?= d($ph['taken_at'], 'j M') ?></figcaption>
              </figure>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <p class="hint" style="margin-bottom:12px">No photos yet.</p>
        <?php endif; ?>

        <form method="post" action="<?= url('/patients/' . $p['id'] . '/photo') ?>" enctype="multipart/form-data">
          <?= Csrf::field() ?>
          <div class="field"><label>Add a photo</label>
            <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></div>
          <div class="field"><label>Type</label>
            <select name="type"><option value="before">Before</option><option value="after">After</option>
              <option value="progress">Progress</option></select></div>
          <label class="check"><input type="checkbox" name="marketing_consent">
            Patient agreed this image may be used in marketing</label>
          <button class="btn ghost sm" type="submit">Upload</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="hd"><h3>Follow-ups</h3></div>
      <div class="tw"><table><tbody>
        <?php if (!$followups): ?><tr><td class="empty">None scheduled.</td></tr><?php endif; ?>
        <?php foreach ($followups as $f): ?>
          <tr>
            <td><?= e(label($f['type'])) ?><small style="display:block;color:var(--faint)"><?= e($f['notes']) ?></small></td>
            <td class="num" style="white-space:nowrap"><?= d($f['due_date'], 'j M y') ?><br>
              <span class="pill <?= statusClass($f['status']) ?>"><?= e(label($f['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>

    <div class="card">
      <div class="hd"><h3>Consent forms</h3>
        <?php if (Auth::can('consents.manage')): ?>
          <a href="<?= url('/consents/create?patient=' . $p['id']) ?>">New →</a>
        <?php endif; ?></div>
      <div class="tw"><table><tbody>
        <?php if (!$consents): ?><tr><td class="empty">None on file.</td></tr><?php endif; ?>
        <?php foreach ($consents as $c): ?>
          <tr>
            <td><a href="<?= url('/consents/' . $c['id']) ?>"><?= e($c['treatment_name']) ?></a>
              <small style="display:block;color:var(--faint)"><?= dt($c['signed_at']) ?></small></td>
            <td class="num"><?= $c['photo_consent'] ? '<span class="pill info">Photo OK</span>' : '' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>

    <?php if ($clinical && $events): ?>
    <div class="card">
      <div class="hd"><h3>Adverse events</h3></div>
      <div class="tw"><table><tbody>
        <?php foreach ($events as $ev): ?>
          <tr><td><strong><?= e($ev['type']) ?></strong>
            <span class="pill <?= statusClass($ev['severity']) ?>"><?= e($ev['severity']) ?></span>
            <small style="display:block;color:var(--faint)"><?= dt($ev['reported_at'], 'j M Y') ?> ·
              <?= $ev['resolved'] ? 'resolved' : 'open' ?></small>
            <div style="font-size:12.5px;color:var(--muted);margin-top:4px"><?= e($ev['description']) ?></div>
          </td></tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>
    <?php endif; ?>

    <?php if (Auth::can('billing.view')): ?>
    <div class="card">
      <div class="hd"><h3>Invoices</h3></div>
      <div class="tw"><table><tbody>
        <?php if (!$invoices): ?><tr><td class="empty">None yet.</td></tr><?php endif; ?>
        <?php foreach ($invoices as $i): ?>
          <tr>
            <td><a href="<?= url('/invoices/' . $i['id']) ?>"><?= e($i['number']) ?></a>
              <small style="display:block;color:var(--faint)"><?= d($i['issued_at']) ?></small></td>
            <td class="num"><?= money($i['total']) ?><br>
              <span class="pill <?= statusClass($i['status']) ?>"><?= e(label($i['status'])) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>
    <?php endif; ?>
  </div>
</div>
