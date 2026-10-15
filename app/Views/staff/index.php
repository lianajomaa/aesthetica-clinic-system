<?php use App\Core\Csrf; ?>

<div class="ph">
  <div><h1>Staff</h1><p class="lead">Accounts and what each role can reach</p></div>
  <div class="actions"><a class="btn" href="<?= url('/staff/create') ?>">+ Add staff</a></div>
</div>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>Name</th><th>Role</th><th>Email</th><th>Last signed in</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($staff as $s): ?>
      <tr<?= $s['is_active'] ? '' : ' style="opacity:.55"' ?>>
        <td><div class="who"><span class="av"><?= e($s['avatar_initials'] ?: initials($s['full_name'])) ?></span>
          <span><span class="nm"><?= e($s['full_name']) ?></span>
            <?php if ($s['practitioner_id']): ?>
              <small><a href="<?= url('/practitioners/' . $s['practitioner_id']) ?>">Practitioner profile</a></small>
            <?php endif; ?></span></div></td>
        <td><span class="pill muted"><?= e($s['role_name']) ?></span></td>
        <td><?= e($s['email']) ?></td>
        <td><?= $s['last_login_at'] ? dt($s['last_login_at']) : 'Never' ?></td>
        <td><span class="pill <?= $s['is_active'] ? 'ok' : 'muted' ?>">
          <?= $s['is_active'] ? 'Active' : 'Disabled' ?></span></td>
        <td class="num">
          <form method="post" action="<?= url('/staff/' . $s['id'] . '/toggle') ?>">
            <?= Csrf::field() ?>
            <button class="btn ghost sm" type="submit"><?= $s['is_active'] ? 'Disable' : 'Enable' ?></button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>

<div class="card">
  <div class="hd"><h3>What each role can do</h3></div>
  <div class="bd">
    <div class="meta">
      <div><div class="k">Owner / Admin</div><div class="v">Everything, including revenue, staff and settings</div></div>
      <div><div class="k">Doctor / Injector</div><div class="v">Own diary, clinical records, consent, stock view</div></div>
      <div><div class="k">Nurse / Assistant</div><div class="v">Patients, history, photos, consent — no pricing</div></div>
      <div><div class="k">Receptionist</div><div class="v">Diary, patients, payments — <strong>no clinical notes</strong></div></div>
      <div><div class="k">Accountant</div><div class="v">Invoices and reports only</div></div>
    </div>
  </div>
</div>
