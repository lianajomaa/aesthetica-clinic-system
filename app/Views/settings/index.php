<?php use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>Settings</h1><p class="lead">Appears on invoices, consent forms and the sign-in page</p></div>
</div>

<form method="post" action="<?= url('/settings') ?>">
  <?= Csrf::field() ?>
  <div class="card" style="max-width:640px"><div class="bd">
    <?php foreach ($keys as $key => $labelText): ?>
      <div class="field">
        <label for="<?= e($key) ?>"><?= e($labelText) ?></label>
        <input type="text" id="<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e($settings[$key] ?? '') ?>">
      </div>
    <?php endforeach; ?>
    <button class="btn" type="submit">Save settings</button>
  </div></div>
</form>
