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
 * @var list<\Zoosper\Admin\Theme\AdminColourTheme> $adminColourThemes
 * @var string $logoutFormHtml
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
<a class="admin-skip-link" href="#admin-content">Skip to main content</a>
<div class="admin-shell" data-admin-shell>
    <aside class="admin-sidebar" id="admin-navigation" data-admin-sidebar aria-label="Primary">
        <a class="brand" href="/admin" aria-label="Zoosper Admin home">
            <img src="/assets/brand/logo.svg" alt="" width="32" height="32">
            <span>Zoosper</span>
        </a>
        <?= $navigation ?>
        <button class="admin-shell-control admin-sidebar-toggle" type="button" data-admin-sidebar-toggle aria-controls="admin-navigation" aria-pressed="false">
            <span aria-hidden="true" data-admin-collapse-icon>‹</span>
            <span class="admin-control-label">Collapse navigation</span>
        </button>
    </aside>
    <button class="admin-navigation-scrim" type="button" data-admin-navigation-close aria-label="Close navigation" aria-hidden="true" tabindex="-1"></button>
    <section class="admin-main">
        <header class="admin-topbar">
            <div class="admin-topbar__leading">
                <button class="admin-shell-control admin-menu-toggle" type="button" data-admin-navigation-toggle aria-controls="admin-navigation" aria-expanded="false">
                    <span aria-hidden="true">☰</span>
                    <span class="admin-control-label">Open navigation</span>
                </button>
                <strong class="admin-topbar__title"><?= $e($title) ?></strong>
            </div>
            <div class="admin-topbar__actions">
                <details class="admin-account-menu">
                    <summary aria-label="Open account menu">
                        <span class="admin-account-avatar" aria-hidden="true"><?= $e(strtoupper(substr(trim($userName), 0, 1)) ?: 'U') ?></span>
                        <span class="admin-account-name"><?= $e($userName) ?></span>
                        <span class="admin-account-chevron" aria-hidden="true">⌄</span>
                    </summary>
                    <div class="admin-account-popover">
                        <p class="admin-account-popover__identity"><strong><?= $e($userName) ?></strong><span>Administrator</span></p>
                        <label class="admin-theme-picker">
                            <span>Colour theme</span>
                            <select data-admin-theme-selector aria-label="Admin colour theme">
                                <?php foreach ($adminColourThemes ?? [] as $adminColourTheme): ?>
                                    <option value="<?= $e($adminColourTheme->code) ?>" data-admin-theme-mode="<?= $e($adminColourTheme->mode) ?>">
                                        <?= $e($adminColourTheme->name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <?= $logoutFormHtml ?? '' ?>
                    </div>
                </details>
            </div>
        </header>
        <?= $slot('before.content') ?>
        <?= $flashMessagesHtml ?? '' ?>
        <main class="admin-content" id="admin-content" tabindex="-1"><?= $content ?></main>
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
