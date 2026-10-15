<div class="ph">
  <div><h1>Practitioners</h1><p class="lead">Who is injecting, and what they have on today</p></div>
</div>

<div class="grid g3">
  <?php foreach ($practitioners as $p): ?>
    <div class="card">
      <div class="bd">
        <div class="who" style="margin-bottom:14px">
          <span class="av" style="width:44px;height:44px;flex:0 0 44px;font-size:14px;
                background:<?= e($p['calendar_colour']) ?>;color:#fff">
            <?= e($p['avatar_initials'] ?: initials($p['full_name'])) ?></span>
          <span>
            <span class="nm" style="font-size:15px"><?= e($p['title']) ?> <?= e($p['full_name']) ?></span>
            <small><?= e($p['speciality'] ?? '') ?></small>
          </span>
        </div>
        <div class="meta">
          <div><div class="k">Today</div><div class="v"><?= (int) $p['today_count'] ?> booked</div></div>
          <div><div class="k">This month</div><div class="v"><?= (int) $p['month_treatments'] ?> treatments</div></div>
        </div>
        <div style="margin-top:14px;display:flex;gap:8px">
          <a class="btn ghost sm" href="<?= url('/practitioners/' . $p['id']) ?>">Open</a>
          <a class="btn ghost sm" href="<?= url('/appointments?practitioner=' . $p['id']) ?>">Diary</a>
        </div>
        <?php if (!$p['is_active']): ?><p class="hint" style="margin-top:10px">Not currently active.</p><?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
