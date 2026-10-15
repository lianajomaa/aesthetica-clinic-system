<div class="ph"><div>
  <h1 style="font-size:52px;font-family:var(--display);color:var(--a1)">500</h1>
  <p class="lead" style="font-size:16px;margin-top:8px"><strong>Something went wrong</strong></p>
</div></div>
<div class="card" style="max-width:560px"><div class="bd">
  <p style="color:var(--muted)"><?= e($message ?? 'An unexpected error occurred. Please try again.') ?></p>
  <a class="btn" href="<?= url('/dashboard') ?>" style="margin-top:14px">Back to dashboard</a>
</div></div>
