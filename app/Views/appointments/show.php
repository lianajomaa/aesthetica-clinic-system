<?php
use App\Core\Auth;
use App\Core\Csrf;
$a = $appt;
?>

<div class="ph">
  <div>
    <h1><?= e($a['treatment_name']) ?></h1>
    <p class="lead"><?= dt($a['starts_at'], 'l j F Y, H:i') ?> – <?= t($a['ends_at']) ?>
      · <?= e($a['practitioner_name']) ?></p>
  </div>
  <div class="actions">
    <span class="pill <?= statusClass($a['status']) ?>" style="padding:8px 14px"><?= e(label($a['status'])) ?></span>
  </div>
</div>

<div class="grid split">
  <div>
    <div class="card">
      <div class="hd"><h3>Patient</h3>
        <a href="<?= url('/patients/' . $a['patient_id']) ?>">Open record →</a></div>
      <div class="bd">
        <div class="meta">
          <div><div class="k">Name</div><div class="v"><?= e($a['patient_name']) ?></div></div>
          <div><div class="k">Code</div><div class="v"><?= e($a['patient_code']) ?></div></div>
          <div><div class="k">Phone</div><div class="v"><?= e($a['phone']) ?></div></div>
          <div><div class="k">Deposit</div><div class="v"><?= money($a['deposit']) ?></div></div>
        </div>
        <?php if ($a['notes']): ?>
          <p style="margin-top:16px;color:var(--muted)"><?= e($a['notes']) ?></p>
        <?php endif; ?>
      </div>
    </div>

    <div class="card">
      <div class="hd"><h3>Visit checklist</h3></div>

      <div class="alert <?= $consent && $consent['signed_at'] ? 'info' : 'warn' ?>">
        <div class="ab"><?= $consent && $consent['signed_at'] ? '✓' : '1' ?></div>
        <div style="flex:1">
          <div class="at">Consent</div>
          <div class="ad">
            <?php if (!$a['requires_consent']): ?>
              Not required for this treatment.
            <?php elseif ($consent && $consent['signed_at']): ?>
              Signed by <?= e($consent['signed_name']) ?> on <?= dt($consent['signed_at']) ?>.
              <a href="<?= url('/consents/' . $consent['id']) ?>">View</a>
            <?php else: ?>
              Not signed yet — this must be completed before the treatment is recorded.
              <?php if (Auth::can('consents.manage')): ?>
                <a href="<?= url('/consents/create?patient=' . $a['patient_id'] . '&treatment=' . $a['treatment_id'] . '&appointment=' . $a['id']) ?>">Sign now</a>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="alert <?= $record ? 'info' : 'warn' ?>">
        <div class="ab"><?= $record ? '✓' : '2' ?></div>
        <div style="flex:1">
          <div class="at">Treatment record</div>
          <div class="ad">
            <?php if ($record): ?>
              Recorded <?= dt($record['performed_at']) ?> ·
              <?= (float) $record['total_units'] > 0 ? qty($record['total_units']) . ' units' : '' ?>
              <?= (float) $record['total_ml'] > 0 ? qty($record['total_ml']) . ' ml' : '' ?>
              <a href="<?= url('/records/' . $record['id']) ?>">View</a>
            <?php else: ?>
              Not recorded yet.
              <?php if (Auth::can('records.manage')): ?>
                <a href="<?= url('/records/create?appointment=' . $a['id']) ?>">Record it</a>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="alert <?= $invoice ? 'info' : 'warn' ?>">
        <div class="ab"><?= $invoice ? '✓' : '3' ?></div>
        <div style="flex:1">
          <div class="at">Payment</div>
          <div class="ad">
            <?php if ($invoice): ?>
              <?= e($invoice['number']) ?> · <?= money($invoice['total']) ?> ·
              <span class="pill <?= statusClass($invoice['status']) ?>"><?= e(label($invoice['status'])) ?></span>
              <a href="<?= url('/invoices/' . $invoice['id']) ?>">Open</a>
            <?php else: ?>
              No invoice raised.
              <?php if (Auth::can('billing.manage')): ?>
                <a href="<?= url('/invoices/create?appointment=' . $a['id']) ?>">Create invoice</a>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div>
    <?php if (Auth::can('appointments.manage')): ?>
    <div class="card">
      <div class="hd"><h3>Update status</h3></div>
      <div class="bd">
        <form method="post" action="<?= url('/appointments/' . $a['id'] . '/status') ?>">
          <?= Csrf::field() ?>
          <div class="field"><label for="status">Status</label>
            <select id="status" name="status">
              <?php foreach ($statuses as $s): ?>
                <option value="<?= $s ?>" <?= $a['status'] === $s ? 'selected' : '' ?>><?= label($s) ?></option>
              <?php endforeach; ?>
            </select></div>
          <button class="btn" type="submit">Save status</button>
        </form>

        <form method="post" action="<?= url('/appointments/' . $a['id'] . '/delete') ?>" style="margin-top:12px"
              onsubmit="return confirm('Cancel this appointment?')">
          <?= Csrf::field() ?>
          <button class="btn ghost" type="submit">Cancel appointment</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="hd"><h3>Price</h3></div>
      <div class="bd">
        <div class="meta">
          <div><div class="k">Treatment</div><div class="v"><?= money($a['price']) ?>
            <?= $a['price_type'] === 'per_unit' ? ' per unit' : ($a['price_type'] === 'per_ml' ? ' per ml' : '') ?></div></div>
          <div><div class="k">Duration</div><div class="v"><?= (int) $a['duration_min'] ?> minutes</div></div>
        </div>
      </div>
    </div>
  </div>
</div>
