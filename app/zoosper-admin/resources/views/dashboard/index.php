<?php
/**
 * @var callable $e
 * @var list<array{code: string, label: string, url: string, icon: string}> $quickLinks
 * @var int $workspaceCount
 */
$quickLinks = $quickLinks ?? [];
$workspaceCount = $workspaceCount ?? count($quickLinks);
?>
<header class="page-header dashboard-hero">
    <div class="page-header__copy">
        <p class="page-header__eyebrow">Admin workspace</p>
        <h1>Dashboard</h1>
        <p class="muted">Open the areas available to your account and continue managing Zoosper.</p>
    </div>
</header>

<section class="dashboard-overview" aria-labelledby="dashboard-overview-title">
    <div class="card admin-stat dashboard-overview__stat">
        <p class="admin-stat__label" id="dashboard-overview-title">Available workspaces</p>
        <p class="admin-stat__value"><?= $e((string) $workspaceCount) ?></p>
        <p class="muted">Shortcuts reflect the current account's permissions and enabled modules.</p>
    </div>
    <div class="card dashboard-overview__guidance">
        <header class="card__header"><h2 class="card__title">Your workspace</h2></header>
        <div class="card__body">
            <p>Use the shortcuts below or the primary navigation to move between Admin areas.</p>
            <p class="muted">Appearance and collapsed-navigation preferences remain local to this browser.</p>
        </div>
    </div>
</section>

<section class="dashboard-workspaces" aria-labelledby="dashboard-workspaces-title">
    <header class="dashboard-section-header">
        <div>
            <p class="page-header__eyebrow">Quick access</p>
            <h2 id="dashboard-workspaces-title">Admin workspaces</h2>
        </div>
        <p class="muted"><?= $e((string) $workspaceCount) ?> available</p>
    </header>
    <?php if ($quickLinks === []): ?>
        <div class="admin-empty-state">
            <h3>No additional workspaces available</h3>
            <p>Your account currently has access to the Dashboard only.</p>
        </div>
    <?php else: ?>
        <nav class="dashboard-links" aria-label="Available Admin workspaces">
            <?php foreach ($quickLinks as $link): ?>
                <a class="dashboard-link" href="<?= $e($link['url']) ?>" data-dashboard-workspace="<?= $e($link['code']) ?>">
                    <span class="dashboard-link__mark" aria-hidden="true"></span>
                    <span class="dashboard-link__copy">
                        <strong><?= $e($link['label']) ?></strong>
                        <span>Open workspace</span>
                    </span>
                    <span class="dashboard-link__arrow" aria-hidden="true">→</span>
                </a>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>
</section>
