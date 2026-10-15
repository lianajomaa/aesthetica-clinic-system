<?php use App\Core\Auth; ?>

<div class="ph">
  <div><h1>Invoices</h1><p class="lead"><?= money($totals['month']) ?> taken this month ·
    <?= money($totals['outstanding']) ?> outstanding</p></div>
  <?php if (Auth::can('billing.manage')): ?>
    <div class="actions"><a class="btn" href="<?= url('/invoices/create') ?>">+ New invoice</a></div>
  <?php endif; ?>
</div>

<div class="tabs">
  <a class="<?= $status === '' ? 'on' : '' ?>" href="<?= url('/invoices') ?>">All</a>
  <a class="<?= $status === 'unpaid' ? 'on' : '' ?>" href="<?= url('/invoices?status=unpaid') ?>">Unpaid</a>
  <a class="<?= $status === 'partial' ? 'on' : '' ?>" href="<?= url('/invoices?status=partial') ?>">Partial</a>
  <a class="<?= $status === 'paid' ? 'on' : '' ?>" href="<?= url('/invoices?status=paid') ?>">Paid</a>
</div>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>Invoice</th><th>Patient</th><th>Issued</th><th class="num">Total</th>
      <th class="num">Paid</th><th>Status</th></tr></thead>
    <tbody>
    <?php if (!$invoices): ?><tr><td colspan="6" class="empty">No invoices here.</td></tr><?php endif; ?>
    <?php foreach ($invoices as $i): ?>
      <tr>
        <td><a href="<?= url('/invoices/' . $i['id']) ?>"><strong><?= e($i['number']) ?></strong></a></td>
        <td><a href="<?= url('/patients/' . $i['patient_id']) ?>"><?= e($i['patient_name']) ?></a>
          <small style="display:block;color:var(--faint)"><?= e($i['code']) ?></small></td>
        <td><?= d($i['issued_at']) ?></td>
        <td class="num"><?= money($i['total']) ?></td>
        <td class="num"><?= money($i['amount_paid']) ?></td>
        <td><span class="pill <?= statusClass($i['status']) ?>"><?= e(label($i['status'])) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
