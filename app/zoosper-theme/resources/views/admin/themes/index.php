<?php
/**
 * @var callable $e
 * @var list<array<string, string>> $themes
 * @var list<\Zoosper\Site\Model\Site> $sites
 * @var string $csrfToken
 */
?>
<h2>Installed Themes</h2>
<table>
    <thead><tr><th>Code</th><th>Name</th><th>Version</th><th>Path</th></tr></thead>
    <tbody>
    <?php if (($themes ?? []) === []): ?>
        <tr><td colspan="4">No installed themes found.</td></tr>
    <?php else: ?>
        <?php foreach ($themes as $theme): ?>
            <tr>
                <td><code><?= $e($theme['code'] ?? '') ?></code></td>
                <td><?= $e($theme['name'] ?? '') ?></td>
                <td><?= $e($theme['version'] ?? '') ?></td>
                <td><?= $e($theme['path'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<h2>Assign Theme to Site</h2>
<?php if (($sites ?? []) === []): ?>
    <p>No active sites found.</p>
<?php else: ?>
    <?php foreach ($sites as $site): ?>
        <form method="post" action="/admin/themes/assign" class="card">
            <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
            <input type="hidden" name="site_id" value="<?= $e($site->id) ?>">
            <h3><?= $e($site->name) ?></h3>
            <label>Theme
                <select name="theme_code">
                    <?php foreach ($themes as $theme): ?>
                        <option value="<?= $e($theme['code'] ?? '') ?>"<?= (($theme['code'] ?? '') === $site->themeCode) ? ' selected' : '' ?>>
                            <?= $e(($theme['name'] ?? '') . ' (' . ($theme['code'] ?? '') . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Save theme</button>
        </form>
    <?php endforeach; ?>
<?php endif; ?>
