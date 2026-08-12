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
 * @var string|null $metaDescription
 * @var string|null $canonicalUrl
 * @var string $robots
 * @var string $openGraphTitle
 * @var string|null $openGraphDescription
 * @var string|null $openGraphUrl
 */
$stylesheetUrl = isset($cdn) ? $cdn->staticAsset('/static/themes/default/assets/css/app.css') : '/static/themes/default/assets/css/app.css';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title ?? 'Zoosper') ?></title>
    <?php if (($metaDescription ?? '') !== ''): ?><meta name="description" content="<?= $e($metaDescription) ?>"><?php endif; ?>
    <meta name="robots" content="<?= $e($robots ?? 'noindex,nofollow') ?>">
    <?php if (($canonicalUrl ?? '') !== ''): ?><link rel="canonical" href="<?= $e($canonicalUrl) ?>"><?php endif; ?>
    <meta property="og:title" content="<?= $e($openGraphTitle ?? $title ?? 'Zoosper') ?>">
    <?php if (($openGraphDescription ?? '') !== ''): ?><meta property="og:description" content="<?= $e($openGraphDescription) ?>"><?php endif; ?>
    <?php if (($openGraphUrl ?? '') !== ''): ?><meta property="og:url" content="<?= $e($openGraphUrl) ?>"><?php endif; ?>
    <link rel="icon" type="image/svg+xml" href="/assets/brand/favicon.svg">
    <link rel="stylesheet" href="<?= $e($stylesheetUrl) ?>">
</head>
<body>
<?= $slot('body.start') ?>
<header class="site-header">
    <a href="/" class="site-logo" aria-label="Zoosper home"><img src="/assets/brand/logo.svg" alt="" width="36" height="36"><span>Zoosper</span></a>
</header>
<main class="site-main page-shell">
    <?= $content ?? '' ?>
</main>
<footer class="site-footer">
    <small>Powered by Zoosper</small>
</footer>
<?= $slot('body.end') ?>
</body>
</html>
