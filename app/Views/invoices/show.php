<?php
use App\Core\Auth;
use App\Core\Csrf;
$i = $invoice;
$due = (float) $i['total'] - (float) $i['amount_paid'];
?>

<div class="ph">
  <div><h1><?= e($i['number']) ?></h1>
    <p class="lead"><?= e($i['patient_name']) ?> (<?= e($i['code']) ?>) · issued <?= d($i['issued_at']) ?></p></div>
  <div class="actions noprint">
    <button class="btn ghost" onclick="window.print()">Print</button>
    <span class="pill <?= statusClass($i['status']) ?>" style="padding:8px 14px"><?= e(label($i['status'])) ?></span>
  </div>
</div>

<div class="grid split">
  <div class="card">
    <div class="hd">
      <div><h3><?= e(setting('clinic_name', 'Aesthetica')) ?></h3>
        <div class="sub"><?= e(setting('clinic_address', '')) ?> · <?= e(setting('clinic_phone', '')) ?></div></div>
    </div>
    <div class="tw">
      <table>
        <thead><tr><th>Description</th><th class="num">Qty</th><th class="num">Unit</th><th class="num">Total</th></tr></thead>
        <tbody>
        <?php foreach ($items as $it): ?>
          <tr><td><?= e($it['description']) ?></td>
            <td class="num"><?= qty($it['quantity']) ?></td>
            <td class="num"><?= money($it['unit_price']) ?></td>
            <td class="num"><?= money($it['line_total']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr><th colspan="3" style="text-align:right">Subtotal</th><th class="num"><?= money($i['subtotal']) ?></th></tr>
          <?php if ((float) $i['discount'] > 0): ?>
            <tr><th colspan="3" style="text-align:right">Discount</th>
              <th class="num">− <?= money($i['discount']) ?></th></tr>
          <?php endif; ?>
          <tr><th colspan="3" style="text-align:right">Total</th>
            <th class="num" style="font-size:16px"><?= money($i['total']) ?></th></tr>
          <tr><th colspan="3" style="text-align:right">Paid</th><th class="num"><?= money($i['amount_paid']) ?></th></tr>
          <?php if ($due > 0.001): ?>
            <tr><th colspan="3" style="text-align:right;color:var(--bad)">Outstanding</th>
              <th class="num" style="color:var(--bad)"><?= money($due) ?></th></tr>
          <?php endif; ?>
        </tfoot>
      </table>
    </div>
  </div>

  <div>
    <?php if (Auth::can('billing.manage') && $due > 0.001): ?>
    <div class="card noprint">
      <div class="hd"><h3>Take a payment</h3></div>
      <div class="bd">
        <form method="post" action="<?= url('/invoices/' . $i['id'] . '/pay') ?>">
          <?= Csrf::field() ?>
          <div class="field"><label for="amount">Amount</label>
            <input type="number" id="amount" name="amount" step="0.01" min="0" required
                   value="<?= number_format($due, 2, '.', '') ?>"></div>
          <div class="field"><label for="method">Method</label>
            <select id="method" name="method">
              <option value="cash">Cash</option><option value="card">Card</option>
              <option value="transfer">Transfer</option><option value="other">Other</option></select></div>
          <div class="field"><label for="reference">Reference</label>
            <input type="text" id="reference" name="reference" placeholder="Optional"></div>
          <button class="btn" type="submit">Record payment</button>
        </form>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="hd"><h3>Payments</h3></div>
      <div class="tw"><table><tbody>
        <?php if (!$payments): ?><tr><td class="empty">Nothing paid yet.</td></tr><?php endif; ?>
        <?php foreach ($payments as $p): ?>
          <tr><td><?= e(label($p['method'])) ?>
            <small style="display:block;color:var(--faint)"><?= dt($p['paid_at']) ?>
              <?= $p['user_name'] ? '· ' . e($p['user_name']) : '' ?></small></td>
            <td class="num"><?= money($p['amount']) ?></td></tr>
        <?php endforeach; ?>
      </tbody></table></div>
    </div>
  </div>
</div>
