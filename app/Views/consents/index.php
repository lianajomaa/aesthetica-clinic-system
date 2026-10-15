<div class="ph">
  <div><h1>Consent forms</h1><p class="lead">Signed and stored against the patient record</p></div>
  <div class="actions"><a class="btn" href="<?= url('/consents/create') ?>">+ New consent</a></div>
</div>

<div class="card"><div class="tw">
  <table>
    <thead><tr><th>Signed</th><th>Patient</th><th>Treatment</th><th>Photo use</th><th>Version</th></tr></thead>
    <tbody>
    <?php if (!$consents): ?><tr><td colspan="5" class="empty">No consent forms yet.</td></tr><?php endif; ?>
    <?php foreach ($consents as $c): ?>
      <tr>
        <td class="time"><a href="<?= url('/consents/' . $c['id']) ?>"><?= dt($c['signed_at'], 'j M Y') ?></a>
          <small style="display:block;color:var(--faint);font-weight:400"><?= t($c['signed_at']) ?></small></td>
        <td><a class="who" href="<?= url('/patients/' . $c['patient_id']) ?>">
          <span class="av"><?= e(initials($c['patient_name'])) ?></span>
          <span><span class="nm"><?= e($c['patient_name']) ?></span><small><?= e($c['code']) ?></small></span></a></td>
        <td><?= e($c['treatment_name']) ?></td>
        <td><?= $c['photo_consent']
              ? '<span class="pill info">Agreed</span>'
              : '<span class="pill muted">Declined</span>' ?></td>
        <td><?= e($c['version']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div></div>
