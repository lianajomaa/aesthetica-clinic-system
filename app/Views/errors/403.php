<div class="ph"><div>
  <h1 style="font-size:52px;font-family:var(--display);color:var(--a1)">403</h1>
  <p class="lead" style="font-size:16px;margin-top:8px"><strong>Not allowed</strong></p>
</div></div>
<div class="card" style="max-width:560px"><div class="bd">
  <p style="color:var(--muted)"><?= e($message ?? 'You do not have permission to view this page. If you think that is wrong, ask the clinic owner to check your role.') ?></p>
  <a class="btn" href="<?= url('/dashboard') ?>" style="margin-top:14px">Back to dashboard</a>
</div></div>
