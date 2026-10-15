<?php use App\Core\Auth; ?>

<div class="ph">
  <div><h1>Treatment menu</h1>
    <p class="lead">Duration drives the diary, price drives the invoice, follow-up days drive the recalls</p></div>
  <?php if (Auth::can('treatments.manage')): ?>
    <div class="actions"><a class="btn" href="<?= url('/treatments/create') ?>">+ New treatment</a></div>
  <?php endif; ?>
</div>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>Treatment</th><th>Category</th><th class="num">Duration</th><th class="num">Price</th>
      <th>Consent</th><th>Review / recall</th><th class="num">Performed</th>
      <?php if (Auth::can('treatments.manage')): ?><th></th><?php endif; ?></tr></thead>
    <tbody>
    <?php foreach ($treatments as $t): ?>
      <tr<?= $t['is_active'] ? '' : ' style="opacity:.5"' ?>>
        <td><strong><?= e($t['name']) ?></strong>
          <?php if (!$t['is_active']): ?><span class="pill muted">Inactive</span><?php endif; ?>
          <?php if ($t['aftercare_title']): ?>
            <small style="display:block;color:var(--faint)"><?= e($t['aftercare_title']) ?></small>
          <?php endif; ?></td>
        <td><span class="pill muted"><?= e(label($t['category'])) ?></span></td>
        <td class="num"><?= (int) $t['duration_min'] ?> min</td>
        <td class="num"><?= money($t['price']) ?>
          <?php if ($t['price_type'] !== 'fixed'): ?>
            <small style="display:block;color:var(--faint)">per <?= $t['price_type'] === 'per_unit' ? 'unit' : 'ml' ?></small>
          <?php endif; ?></td>
        <td><?= $t['requires_consent'] ? '<span class="pill warn">Required</span>' : '<span class="pill muted">Not needed</span>' ?></td>
        <td><?= $t['followup_days'] ? (int) $t['followup_days'] . ' d' : '—' ?>
          / <?= $t['recall_days'] ? (int) $t['recall_days'] . ' d' : '—' ?></td>
        <td class="num"><?= (int) $t['performed'] ?></td>
        <?php if (Auth::can('treatments.manage')): ?>
          <td class="num"><a class="btn ghost sm" href="<?= url('/treatments/' . $t['id'] . '/edit') ?>">Edit</a></td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
