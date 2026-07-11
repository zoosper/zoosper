<?php

declare(strict_types=1);

/**
 * Default frontend layout template.
 *
 * The render context provides `$siteContext`, `$cdn`, `$cacheContext` and
 * `$cacheKeys` through TemplateViewContextProvider. This template uses CDN-aware
 * URL helpers for static assets and dynamic links so store-view logic is not
 * hard-coded in frontend templates.
 *
 * Available variables are supplied by the page renderer/template renderer:
 *
 * @var object|null $page
 * @var object|null $site
 * @var string|null $content
 * @var string|null $versionLabel
 * @var \Zoosper\Core\Site\SiteContext|null $siteContext
 * @var \Zoosper\Core\Url\CdnUrlResolver|null $cdn
 * @var callable $e
 */

$title = isset($page) && is_object($page) && property_exists($page, 'metaTitle') && (string) $page->metaTitle !== ''
    ? (string) $page->metaTitle
    : ((isset($page) && is_object($page) && property_exists($page, 'title')) ? (string) $page->title : ((isset($site) && is_object($site) && property_exists($site, 'name')) ? (string) $site->name : 'Zoosper'));

$siteName = isset($site) && is_object($site) && property_exists($site, 'name') ? (string) $site->name : 'Zoosper';
$homeUrl = isset($cdn, $siteContext) ? $cdn->dynamicForContext('/', $siteContext) : '/';
$stylesheetUrl = isset($cdn) ? $cdn->staticAsset('/themes/default/assets/css/app.css') : '/themes/default/assets/css/app.css';
?>
<!doctype html>
<html lang="<?= $e($siteContext?->locale ?? 'en_AU') ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title) ?></title>
    <link rel="stylesheet" href="<?= $e($stylesheetUrl) ?>">
</head>
<body class="zoosper-frontend">
<header class="site-header">
    <div class="site-header__inner">
        <a class="site-header__brand" href="<?= $e($homeUrl) ?>"><?= $e($siteName) ?></a>
        <?php if (isset($versionLabel) && $versionLabel !== ''): ?>
            <span class="site-header__version"><?= $e($versionLabel) ?></span>
        <?php endif; ?>
    </div>
</header>

<main class="site-main" id="main-content">
    <?= $content ?? '' ?>
</main>

<footer class="site-footer">
    <small>&copy; <?= $e((string) date('Y')) ?> <?= $e($siteName) ?></small>
</footer>
</body>
</html>
