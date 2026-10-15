<?php
use App\Core\Csrf;
$t = $treatment;
?>

<div class="ph">
  <div><h1><?= $t ? 'Edit treatment' : 'New treatment' ?></h1></div>
  <div class="actions"><a class="btn ghost" href="<?= url('/treatments') ?>">Cancel</a></div>
</div>

<form method="post" action="<?= $t ? url('/treatments/' . $t['id']) : url('/treatments') ?>">
  <?= Csrf::field() ?>
  <div class="grid g2">
    <div class="card"><div class="bd">
      <fieldset><legend>Basics</legend>
        <div class="field"><label for="name">Name</label>
          <input type="text" id="name" name="name" required value="<?= old('name', $t['name'] ?? '') ?>"></div>
        <div class="row2">
          <div class="field"><label for="category">Category</label>
            <select id="category" name="category">
              <?php foreach (['toxin','filler','thread','skinbooster','consultation','other'] as $c): ?>
                <option value="<?= $c ?>" <?= ($t['category'] ?? '') === $c ? 'selected' : '' ?>><?= label($c) ?></option>
              <?php endforeach; ?>
            </select></div>
          <div class="field"><label for="duration_min">Duration (minutes)</label>
            <input type="number" id="duration_min" name="duration_min" required min="5" step="5"
                   value="<?= old('duration_min', $t['duration_min'] ?? 30) ?>"></div>
        </div>
        <div class="row2">
          <div class="field"><label for="price">Price</label>
            <input type="number" id="price" name="price" step="0.01" min="0"
                   value="<?= old('price', $t['price'] ?? '0') ?>"></div>
          <div class="field"><label for="price_type">Priced</label>
            <select id="price_type" name="price_type">
              <?php foreach (['fixed' => 'Fixed price', 'per_unit' => 'Per unit', 'per_ml' => 'Per ml'] as $k => $v): ?>
                <option value="<?= $k ?>" <?= ($t['price_type'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
              <?php endforeach; ?>
            </select></div>
        </div>
        <div class="field"><label for="description">Description</label>
          <textarea id="description" name="description"><?= e($t['description'] ?? '') ?></textarea></div>
      </fieldset>
    </div></div>

    <div class="card"><div class="bd">
      <fieldset><legend>Clinical rules</legend>
        <label class="check"><input type="checkbox" name="requires_consent"
          <?= ($t === null || $t['requires_consent']) ? 'checked' : '' ?>>
          A signed consent form is required before this treatment can be recorded</label>

        <div class="row2" style="margin-top:12px">
          <div class="field"><label for="followup_days">Review after (days)</label>
            <input type="number" id="followup_days" name="followup_days" min="0"
                   value="<?= old('followup_days', $t['followup_days'] ?? '') ?>" placeholder="e.g. 14">
            <p class="hint">A follow-up is raised automatically.</p></div>
          <div class="field"><label for="recall_days">Recall after (days)</label>
            <input type="number" id="recall_days" name="recall_days" min="0"
                   value="<?= old('recall_days', $t['recall_days'] ?? '') ?>" placeholder="e.g. 120">
            <p class="hint">When to invite them back.</p></div>
        </div>

        <div class="field"><label for="aftercare_id">Aftercare sheet</label>
          <select id="aftercare_id" name="aftercare_id">
            <option value="">None</option>
            <?php foreach ($aftercare as $ac): ?>
              <option value="<?= (int) $ac['id'] ?>" <?= (int) ($t['aftercare_id'] ?? 0) === (int) $ac['id'] ? 'selected' : '' ?>>
                <?= e($ac['title']) ?></option>
            <?php endforeach; ?>
          </select></div>

        <label class="check"><input type="checkbox" name="is_active"
          <?= ($t === null || $t['is_active']) ? 'checked' : '' ?>> Available to book</label>

        <button class="btn" type="submit" style="margin-top:10px"><?= $t ? 'Save changes' : 'Add treatment' ?></button>
      </fieldset>
    </div></div>
  </div>
</form>
