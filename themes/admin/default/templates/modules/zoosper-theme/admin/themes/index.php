<?php
/**
 * Admin theme override for zoosper-theme::admin/themes/index.
 * @var callable $e
 * @var list<array<string, string>> $themes
 * @var list<\Zoosper\Site\Model\Site> $sites
 * @var string $csrfToken
 * @var string $assignUrl
 */
?>
<header class="page-header">
    <div class="page-header__copy">
        <p class="page-header__eyebrow">Appearance</p>
        <h2>Theme workspace</h2>
        <p class="muted">Review installed packages and choose the active frontend theme for each site.</p>
    </div>
</header>

<section aria-labelledby="installed-themes-title">
    <div class="card__header">
        <div>
            <h2 id="installed-themes-title" class="card__title">Installed themes</h2>
            <p class="muted"><?= count($themes ?? []) ?> available</p>
        </div>
    </div>
    <div class="admin-table-scroll">
        <table>
            <thead>
                <tr><th scope="col">Theme</th><th scope="col">Code</th><th scope="col">Version</th><th scope="col">Source</th></tr>
            </thead>
            <tbody>
            <?php if (($themes ?? []) === []): ?>
                <tr><td colspan="4" class="admin-table-empty">No installed themes found.</td></tr>
            <?php else: ?>
                <?php foreach ($themes as $theme): ?>
                    <tr>
                        <td><strong><?= $e($theme['name'] ?? '') ?></strong></td>
                        <td><code><?= $e($theme['code'] ?? '') ?></code></td>
                        <td><?= $e($theme['version'] ?? '') ?></td>
                        <td><span class="muted"><?= $e($theme['path'] ?? '') ?></span></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section aria-labelledby="site-theme-title">
    <div class="page-header">
        <div class="page-header__copy">
            <h2 id="site-theme-title">Site assignments</h2>
            <p class="muted">Each save affects only the selected active site.</p>
        </div>
    </div>
    <?php if (($sites ?? []) === []): ?>
        <div class="admin-empty-state" role="status">
            <h3>No active sites</h3>
            <p>Activate a site before assigning a frontend theme.</p>
        </div>
    <?php elseif (($themes ?? []) === []): ?>
        <div class="admin-empty-state" role="status">
            <h3>No themes available</h3>
            <p>Install a valid theme package before changing site assignments.</p>
        </div>
    <?php else: ?>
        <div class="admin-card-grid">
            <?php foreach ($sites as $site): ?>
                <?php $themeSelectId = 'site-theme-' . $site->id; ?>
                <form method="post" action="<?= $e($assignUrl) ?>" class="card">
                    <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                    <input type="hidden" name="site_id" value="<?= $e($site->id) ?>">
                    <div class="card__header">
                        <div>
                            <h3 class="card__title"><?= $e($site->name) ?></h3>
                            <span class="admin-badge">Active site</span>
                        </div>
                    </div>
                    <div class="card__body">
                        <label for="<?= $e($themeSelectId) ?>">Frontend theme</label>
                        <select id="<?= $e($themeSelectId) ?>" name="theme_code">
                            <?php foreach ($themes as $theme): ?>
                                <option value="<?= $e($theme['code'] ?? '') ?>"<?= (($theme['code'] ?? '') === $site->themeCode) ? ' selected' : '' ?>>
                                    <?= $e(($theme['name'] ?? '') . ' (' . ($theme['code'] ?? '') . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="actions">
                            <button type="submit">Save theme</button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>



