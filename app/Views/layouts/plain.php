<?php use App\Core\Flash; ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title ?? '') ?> · Aesthetica</title>
<link rel="icon" href="<?= url('assets/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/app.css') ?>">
</head>
<body>
<?php foreach (Flash::take() as $f): ?>
  <div class="flash <?= e($f['type']) ?>" style="margin:0;border-radius:0;justify-content:center"><?= e($f['message']) ?></div>
<?php endforeach; ?>
<?= $content ?>
</body>
</html>
