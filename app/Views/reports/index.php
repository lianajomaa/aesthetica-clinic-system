<?php
$s = $summary;
$margin = $s['revenue'] > 0 ? round((($s['revenue'] - $s['product_cost']) / $s['revenue']) * 100) : 0;
$totalPatients = $retention['returning'] + $retention['once'];
$rate = $totalPatients > 0 ? round(($retention['returning'] / $totalPatients) * 100) : 0;
$maxMonth = max(1, max(array_map(static fn ($m) => (float) $m['revenue'], $monthly ?: [['revenue' => 1]])));
?>

<div class="ph">
  <div><h1>Reports</h1><p class="lead"><?= d($from) ?> to <?= d($to) ?></p></div>
  <div class="actions">
    <form method="get" action="<?= url('/reports') ?>" style="display:flex;gap:8px;align-items:flex-end">
      <div class="field" style="margin:0"><label for="from">From</label>
        <input type="date" id="from" name="from" value="<?= e($from) ?>"></div>
      <div class="field" style="margin:0"><label for="to">To</label>
        <input type="date" id="to" name="to" value="<?= e($to) ?>"></div>
      <button class="btn ghost" type="submit">Apply</button>
    </form>
  </div>
</div>

<div class="grid g4">
  <div class="stat"><div class="k">Revenue</div><div class="v"><?= money($s['revenue']) ?></div>
    <div class="t">product cost <?= money($s['product_cost']) ?> · <b><?= $margin ?>% margin</b></div></div>
  <div class="stat"><div class="k">Treatments</div><div class="v"><?= $s['treatments'] ?></div>
    <div class="t">across <?= $s['appointments'] ?> appointments</div></div>
  <div class="stat"><div class="k">New patients</div><div class="v"><?= $s['new_patients'] ?></div>
    <div class="t"><b><?= $rate ?>%</b> of all patients return</div></div>
  <div class="stat"><div class="k">No-shows</div><div class="v"><?= $s['no_shows'] ?></div>
    <div class="t"><?= $s['appointments'] > 0 ? round(($s['no_shows'] / $s['appointments']) * 100, 1) : 0 ?>% of bookings</div></div>
</div>

<div class="grid split" style="margin-top:16px">
  <div>
    <div class="card">
      <div class="hd"><h3>Revenue by month</h3></div>
      <div class="bd">
        <?php if (!$monthly): ?><p class="hint">No payments in this period.</p><?php endif; ?>
        <?php foreach ($monthly as $m): ?>
          <div style="margin-bottom:13px">
            <div style="display:flex;justify-content:space-between;font-size:13px">
              <span><?= d($m['month'] . '-01', 'F Y') ?></span>
              <strong><?= money($m['revenue']) ?></strong></div>
            <div class="bar"><i style="width:<?= round(((float) $m['revenue'] / $maxMonth) * 100) ?>%"></i></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <div class="hd"><h3>By treatment</h3></div>
      <div class="tw"><table>
        <thead><tr><th>Treatment</th><th class="num">Done</th><th class="num">Units</th><th class="num">Revenue</th></tr></thead>
        <tbody>
        <?php if (!$byTreatment): ?><tr><td colspan="4" class="empty">Nothing in this period.</td></tr><?php endif; ?>
        <?php foreach ($byTreatment as $t): ?>
          <tr><td><?= e($t['name']) ?>
            <small style="display:block;color:var(--faint)"><?= e(label($t['category'])) ?></small></td>
            <td class="num"><?= (int) $t['performed'] ?></td>
            <td class="num"><?= (float) $t['units'] > 0 ? qty($t['units']) : '—' ?></td>
            <td class="num"><?= money($t['revenue']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>

    <div class="card">
      <div class="hd"><div><h3>Product consumption</h3>
        <div class="sub">What you used, and what it cost you</div></div></div>
      <div class="tw"><table>
        <thead><tr><th>Product</th><th class="num">Used</th><th class="num">Cost</th></tr></thead>
        <tbody>
        <?php if (!$productUse): ?><tr><td colspan="3" class="empty">No product used in this period.</td></tr><?php endif; ?>
        <?php foreach ($productUse as $p): ?>
          <tr><td><?= e($p['name']) ?>
            <small style="display:block;color:var(--faint)"><?= e($p['brand']) ?></small></td>
            <td class="num"><?= qty($p['used']) ?> <?= e($p['unit']) ?></td>
            <td class="num"><?= money($p['cost']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="hd"><h3>By practitioner</h3></div>
      <div class="bd">
        <?php
        $maxP = max(1, max(array_map(static fn ($p) => (float) $p['revenue'], $byPractitioner ?: [['revenue' => 1]])));
        foreach ($byPractitioner as $p): ?>
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;font-size:13px">
              <span><span class="dot" style="background:<?= e($p['calendar_colour']) ?>"></span><?= e($p['full_name']) ?></span>
              <strong><?= money($p['revenue']) ?></strong></div>
            <div class="bar"><i style="width:<?= round(((float) $p['revenue'] / $maxP) * 100) ?>%"></i></div>
            <div style="font-size:11.5px;color:var(--faint);margin-top:4px">
              <?= (int) $p['treatments'] ?> treatments
              <?php if ((float) $p['commission_rate'] > 0): ?>
                · owed <?= money((float) $p['revenue'] * (float) $p['commission_rate'] / 100) ?>
              <?php endif; ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card">
      <div class="hd"><div><h3>Where patients came from</h3><div class="sub">New registrations</div></div></div>
      <div class="tw"><table><tbody>
        <?php if (!$bySource): ?><tr><td class="empty">No new patients in this period.</td></tr><?php endif; ?>
        <?php foreach ($bySource as $src): ?>
          <tr><td><?= e(label($src['source'])) ?></td><td class="num"><?= (int) $src['total'] ?></td></tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>

    <div class="card">
      <div class="hd"><h3>Retention</h3></div>
      <div class="bd">
        <div class="meta">
          <div><div class="k">Returning</div><div class="v"><?= $retention['returning'] ?> patients</div></div>
          <div><div class="k">One visit only</div><div class="v"><?= $retention['once'] ?> patients</div></div>
        </div>
        <div class="bar" style="margin-top:12px"><i style="width:<?= $rate ?>%"></i></div>
        <p class="hint"><?= $rate ?>% of patients have come back at least once.</p>
      </div>
    </div>
  </div>
</div>
