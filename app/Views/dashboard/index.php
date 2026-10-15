<?php use App\Core\Auth; ?>

<div class="ph">
  <div>
    <h1>Good <?= date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') ?>,
      <?= e(explode(' ', Auth::user()['full_name'])[0] === 'Dr.'
            ? 'Dr. ' . explode(' ', Auth::user()['full_name'])[1]
            : explode(' ', Auth::user()['full_name'])[0]) ?></h1>
    <p class="lead"><?= date('l j F') ?> · <?= $stats['today_total'] ?> appointment<?= $stats['today_total'] === 1 ? '' : 's' ?>
      today<?= $stats['today_consults'] ? ' · ' . $stats['today_consults'] . ' consultation' . ($stats['today_consults'] === 1 ? '' : 's') : '' ?></p>
  </div>
  <div class="actions">
    <a class="btn ghost" href="<?= url('/appointments') ?>">Today’s diary</a>
    <?php if (Auth::can('records.manage')): ?>
      <a class="btn" href="<?= url('/records/create') ?>">Record a treatment</a>
    <?php endif; ?>
  </div>
</div>

<div class="grid g4">
  <div class="stat">
    <div class="k">Today</div>
    <div class="v"><?= $stats['today_total'] ?></div>
    <div class="t"><?= $stats['today_total'] - $stats['today_consults'] ?> treatments ·
      <b><?= $stats['today_consults'] ?> consultations</b></div>
  </div>

  <?php if (Auth::can('reports.view')): ?>
  <div class="stat">
    <div class="k">This week</div>
    <div class="v"><?= money($stats['week_revenue']) ?></div>
    <div class="t">
      <?php if ($stats['revenue_change'] === null): ?>
        no comparison available
      <?php else: ?>
        <b class="<?= $stats['revenue_change'] < 0 ? 'down' : '' ?>">
          <?= $stats['revenue_change'] >= 0 ? '↑' : '↓' ?> <?= abs($stats['revenue_change']) ?>%</b>
        vs last week
      <?php endif; ?>
    </div>
  </div>
  <?php else: ?>
  <div class="stat">
    <div class="k">New patients</div>
    <div class="v"><?= $stats['new_patients'] ?></div>
    <div class="t">this month</div>
  </div>
  <?php endif; ?>

  <div class="stat">
    <div class="k">Toxin units</div>
    <div class="v"><?= qty($stats['units_month']) ?>u</div>
    <div class="t">this month · <b><?= qty(round($stats['units_month'] / 100, 1)) ?> vials</b></div>
  </div>

  <div class="stat">
    <div class="k">Follow-ups due</div>
    <div class="v"><?= $stats['followups_due'] ?></div>
    <div class="t"><?php if ($stats['followups_over']): ?>
        <b class="down"><?= $stats['followups_over'] ?> overdue</b> · next 7 days
      <?php else: ?>none overdue · next 7 days<?php endif; ?></div>
  </div>
</div>

<div class="grid split" style="margin-top:16px">
  <div>
    <div class="card">
      <div class="hd">
        <div><h3>Today’s schedule</h3><div class="sub"><?= date('l j F Y') ?></div></div>
        <a href="<?= url('/appointments') ?>">Full diary →</a>
      </div>
      <div class="tw">
        <table>
          <thead><tr><th>Time</th><th>Patient</th><th>Treatment</th><th>Practitioner</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (!$todayList): ?>
            <tr><td colspan="5" class="empty">Nothing booked today.</td></tr>
          <?php endif; ?>
          <?php foreach ($todayList as $a): ?>
            <tr>
              <td class="time"><?= t($a['starts_at']) ?></td>
              <td>
                <a class="who" href="<?= url('/patients/' . $a['patient_id']) ?>">
                  <span class="av"><?= e(initials($a['patient_name'])) ?></span>
                  <span><span class="nm"><?= e($a['patient_name']) ?></span>
                    <small><?= e($a['patient_code']) ?></small></span>
                </a>
              </td>
              <td>
                <a href="<?= url('/appointments/' . $a['id']) ?>"><?= e($a['treatment_name']) ?></a>
                <?php if ((int) $a['requires_consent'] === 1 && (int) $a['consent_signed'] === 0
                          && !in_array($a['status'], ['cancelled','no_show'], true)): ?>
                  <br><span class="pill warn" style="margin-top:4px">Consent pending</span>
                <?php endif; ?>
              </td>
              <td style="white-space:nowrap">
                <span class="dot" style="background:<?= e($a['calendar_colour']) ?>"></span>
                <?= e($a['practitioner_name']) ?>
              </td>
              <td><span class="pill <?= statusClass($a['status']) ?>"><?= e(label($a['status'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if (Auth::can('reports.view') && $topTreatments): ?>
    <div class="card">
      <div class="hd"><div><h3>Top treatments</h3><div class="sub">Last 90 days</div></div>
        <a href="<?= url('/reports') ?>">Reports →</a></div>
      <div class="tw">
        <table>
          <thead><tr><th>Treatment</th><th class="num">Performed</th><th class="num">Revenue</th></tr></thead>
          <tbody>
          <?php foreach ($topTreatments as $t): ?>
            <tr>
              <td><?= e($t['name']) ?> <span class="pill muted"><?= e(label($t['category'])) ?></span></td>
              <td class="num"><?= (int) $t['performed'] ?></td>
              <td class="num"><?= money($t['revenue']) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div>
    <div class="card">
      <div class="hd"><h3>Needs attention</h3></div>

      <?php
      $none = true;
      foreach ($alerts['unsigned_consents'] as $c): $none = false; ?>
        <div class="alert warn">
          <div class="ab">!</div>
          <div><div class="at">Consent not signed — <?= e($c['patient_name']) ?></div>
            <div class="ad"><?= e($c['treatment_name']) ?> at <?= t($c['starts_at']) ?>.
              <a href="<?= url('/consents/create?patient=' . $c['patient_id'] . '&treatment=' . $c['treatment_id'] . '&appointment=' . $c['id']) ?>">Sign now</a></div></div>
        </div>
      <?php endforeach; ?>

      <?php foreach ($alerts['expiring'] as $b): $none = false; ?>
        <div class="alert <?= $b['days_left'] < 0 ? 'bad' : 'warn' ?>">
          <div class="ab"><?= $b['days_left'] < 0 ? '×' : '!' ?></div>
          <div><div class="at"><?= e($b['name']) ?> — lot <?= e($b['lot_number']) ?></div>
            <div class="ad"><?= $b['days_left'] < 0
                ? 'Expired ' . abs((int) $b['days_left']) . ' days ago'
                : 'Expires in ' . (int) $b['days_left'] . ' days' ?> ·
              <?= qty($b['qty_remaining']) ?> <?= e($b['unit']) ?> left</div></div>
        </div>
      <?php endforeach; ?>

      <?php foreach ($alerts['low_stock'] as $p): $none = false; ?>
        <div class="alert bad">
          <div class="ab">↓</div>
          <div><div class="at">Low stock — <?= e($p['name']) ?></div>
            <div class="ad"><?= qty($p['remaining']) ?> <?= e($p['unit']) ?> remaining,
              reorder at <?= qty($p['reorder_level']) ?>.
              <?php if (Auth::can('stock.manage')): ?>
                <a href="<?= url('/stock/batches/create') ?>">Receive stock</a>
              <?php endif; ?></div></div>
        </div>
      <?php endforeach; ?>

      <?php foreach ($alerts['overdue_followups'] as $f): $none = false; ?>
        <div class="alert info">
          <div class="ab">↻</div>
          <div><div class="at">Overdue <?= e($f['type'] === 'recall' ? 'recall' : 'review') ?> — <?= e($f['patient_name']) ?></div>
            <div class="ad">Due <?= d($f['due_date']) ?> · <?= e($f['phone']) ?></div></div>
        </div>
      <?php endforeach; ?>

      <?php if ($none): ?>
        <div class="empty">Nothing needs attention. Everything is up to date.</div>
      <?php endif; ?>
    </div>

    <?php if ($alerts['recalls_due']): ?>
    <div class="card">
      <div class="hd"><div><h3>Recalls coming up</h3><div class="sub">Patients due a repeat treatment</div></div>
        <a href="<?= url('/followups?filter=recall') ?>">All →</a></div>
      <div class="tw"><table><tbody>
        <?php foreach ($alerts['recalls_due'] as $r): ?>
          <tr>
            <td><a href="<?= url('/patients/' . $r['patient_id']) ?>"><?= e($r['patient_name']) ?></a>
              <small style="display:block;color:var(--faint)"><?= e($r['phone']) ?></small></td>
            <td class="num" style="white-space:nowrap"><?= d($r['due_date'], 'j M') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>
    <?php endif; ?>

    <?php if (Auth::can('reports.view')): ?>
    <div class="card">
      <div class="hd"><div><h3>By practitioner</h3><div class="sub">This month</div></div></div>
      <div class="bd">
        <?php
        $max = max(1, max(array_map(static fn ($p) => (float) $p['revenue'], $byPractitioner ?: [['revenue' => 1]])));
        foreach ($byPractitioner as $p): ?>
          <div style="margin-bottom:14px">
            <div style="display:flex;justify-content:space-between;font-size:13px">
              <span><span class="dot" style="background:<?= e($p['calendar_colour']) ?>"></span><?= e($p['full_name']) ?></span>
              <strong><?= money($p['revenue']) ?></strong>
            </div>
            <div class="bar"><i style="width:<?= round(((float) $p['revenue'] / $max) * 100) ?>%"></i></div>
            <div style="font-size:11.5px;color:var(--faint);margin-top:4px">
              <?= (int) $p['treatments'] ?> treatments
              <?php if ((float) $p['commission_rate'] > 0): ?>
                · commission <?= qty($p['commission_rate']) ?>% =
                <?= money((float) $p['revenue'] * (float) $p['commission_rate'] / 100) ?>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
