<?php $r = $record; ?>

<div class="ph">
  <div>
    <h1><?= e($r['treatment_name']) ?></h1>
    <p class="lead"><a href="<?= url('/patients/' . $r['patient_id']) ?>"><?= e($r['patient_name']) ?></a>
      · <?= e($r['patient_code']) ?> · <?= dt($r['performed_at'], 'l j F Y, H:i') ?>
      · <?= e($r['practitioner_name']) ?></p>
  </div>
  <div class="actions noprint">
    <button class="btn ghost" onclick="window.print()">Print</button>
    <a class="btn ghost" href="<?= url('/patients/' . $r['patient_id']) ?>">Patient record</a>
  </div>
</div>

<div class="grid split">
  <div>
    <div class="card">
      <div class="hd"><div><h3>What was given</h3>
        <div class="sub">Product, batch and dose at each site</div></div></div>
      <div class="tw">
        <table>
          <thead><tr><th>Site</th><th>Product</th><th>Lot</th><th>Expiry</th><th class="num">Dose</th></tr></thead>
          <tbody>
          <?php if (!$items): ?><tr><td colspan="5" class="empty">No sites recorded.</td></tr><?php endif; ?>
          <?php foreach ($items as $i): ?>
            <tr>
              <td><strong><?= e($i['injection_site']) ?></strong></td>
              <td><?= e($i['name']) ?><small style="display:block;color:var(--faint)"><?= e($i['brand']) ?></small></td>
              <td><code><?= e($i['lot_number']) ?></code></td>
              <td><?= d($i['expiry_date'], 'M Y') ?></td>
              <td class="num"><strong><?= qty($i['quantity']) ?></strong> <?= e($i['unit']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <?php if ($items): ?>
          <tfoot><tr>
            <th colspan="4" style="text-align:right">Total</th>
            <th class="num">
              <?= (float) $r['total_units'] > 0 ? qty($r['total_units']) . ' units' : '' ?>
              <?= (float) $r['total_ml'] > 0 ? qty($r['total_ml']) . ' ml' : '' ?>
            </th>
          </tr></tfoot>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <?php if ($r['notes'] || $r['technique']): ?>
    <div class="card">
      <div class="hd"><h3>Clinical notes</h3></div>
      <div class="bd">
        <?php if ($r['technique']): ?>
          <div class="meta" style="margin-bottom:12px">
            <div><div class="k">Technique</div><div class="v"><?= e($r['technique']) ?></div></div>
          </div>
        <?php endif; ?>
        <?php if ($r['notes']): ?><p style="color:var(--muted)"><?= nl2br(e($r['notes'])) ?></p><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div>
    <?php if ($followups): ?>
    <div class="card">
      <div class="hd"><h3>Scheduled follow-ups</h3></div>
      <div class="tw"><table><tbody>
        <?php foreach ($followups as $f): ?>
          <tr><td><?= e(label($f['type'])) ?>
            <small style="display:block;color:var(--faint)"><?= e($f['notes']) ?></small></td>
            <td class="num"><?= d($f['due_date'], 'j M y') ?><br>
              <span class="pill <?= statusClass($f['status']) ?>"><?= e(label($f['status'])) ?></span></td></tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>
    <?php endif; ?>

    <?php if ($r['aftercare_body']): ?>
    <div class="card">
      <div class="hd"><div><h3>Aftercare given</h3><div class="sub"><?= e($r['aftercare_title']) ?></div></div></div>
      <div class="bd"><p style="color:var(--muted);white-space:pre-line"><?= e($r['aftercare_body']) ?></p></div>
    </div>
    <?php endif; ?>
  </div>
</div>
