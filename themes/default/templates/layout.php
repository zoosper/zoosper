<?php
/**
 * Default frontend layout.
 *
 * @var callable $e
 * @var callable $slot
 * @var string $title
 * @var string $content Sanitised CMS/page body HTML. This is intentionally
 *                     rendered without escaping because HTML sanitisation is
 *                     enforced before persistence and verified by tooling.
 * @var mixed|null $cdn
 */
$stylesheetUrl = isset($cdn) ? $cdn->staticAsset('/static/themes/default/assets/css/app.css') : '/static/themes/default/assets/css/app.css';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title ?? 'Zoosper') ?></title>
    <link rel="stylesheet" href="<?= $e($stylesheetUrl) ?>">
</head>
<body>
<?= $slot('body.start') ?>
<header class="site-header">
    <a href="/" class="site-logo">Zoosper</a>
</header>
<main class="site-main">
    <?= $content ?? '' ?>
</main>
<footer class="site-footer">
    <small>Powered by Zoosper</small>
</footer>
<?= $slot('body.end') ?>
</body>
</html>
