<?php
/**
 * @var callable $e
 * @var callable $partial
 * @var callable $slot
 * @var string $title
 * @var string $navigation
 * @var string $content
 * @var string $userName
 * @var string $version
 * @var list<\Zoosper\Admin\Asset\AdminAsset> $stylesheets
 * @var list<\Zoosper\Admin\Asset\AdminAsset> $scripts
 * @var string $assetStylesHtml
 * @var string $assetScriptsHtml
 * @var string $flashMessagesHtml
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($title) ?> - Zoosper Admin</title>
    <link rel="icon" type="image/svg+xml" href="/assets/brand/favicon.svg">
    <?php if (($assetStylesHtml ?? '') !== ''): ?>
        <?= $assetStylesHtml ?>
    <?php else: ?>
        <?= $partial('components/layout/admin-assets.php', [
            'stylesheets' => $stylesheets ?? [],
            'scripts' => [],
        ]) ?>
    <?php endif; ?>
</head>
<body>
<?= $slot('body.start') ?>
<div class="admin-shell">
    <aside class="admin-sidebar"><a class="brand" href="/admin" aria-label="Zoosper Admin home"><img src="/assets/brand/logo.svg" alt="" width="32" height="32"><span>Zoosper</span></a><?= $navigation ?></aside>
    <section class="admin-main">
        <header class="admin-topbar"><strong><?= $e($title) ?></strong><span class="muted"><?= $e($userName) ?></span></header>
        <?= $slot('before.content') ?>
        <?= $flashMessagesHtml ?? '' ?>
        <main class="admin-content"><?= $content ?></main>
        <?= $slot('after.content') ?>
    </section>
</div>
<?= $partial('footer.php') ?>
<?php if (($assetScriptsHtml ?? '') !== ''): ?>
    <?= $assetScriptsHtml ?>
<?php else: ?>
    <?= $partial('components/layout/admin-assets.php', [
        'stylesheets' => [],
        'scripts' => $scripts ?? [],
    ]) ?>
<?php endif; ?>
<?= $slot('body.end') ?>
</body>
</html>
