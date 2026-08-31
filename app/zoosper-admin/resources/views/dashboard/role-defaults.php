<?php
/**
 * @var callable $e
 * @var list<Zoosper\AdminDashboard\DashboardRole> $roles
 * @var ?Zoosper\AdminDashboard\DashboardRole $selectedRole
 * @var list<Zoosper\AdminDashboard\DashboardWidget> $availableWidgets
 * @var list<string> $hiddenWidgetCodes
 * @var bool $roleDefaultConfigured
 * @var string $csrfToken
 * @var string $roleDefaultsUrl
 * @var string $saveRoleDefaultsUrl
 * @var string $resetRoleDefaultsUrl
 * @var string $dashboardUrl
 */
$roles = $roles ?? [];
$availableWidgets = $availableWidgets ?? [];
$hiddenWidgetCodes = $hiddenWidgetCodes ?? [];
?>
<header class="page-header">
    <div class="page-header__copy">
        <p class="page-header__eyebrow">Dashboard governance</p>
        <h1>Role defaults</h1>
        <p class="muted">Set the initial Dashboard visibility and order for assigned roles. Permissions remain authoritative, and a user's saved layout takes precedence.</p>
    </div>
    <div class="actions"><a class="button button--secondary" href="<?= $e($dashboardUrl) ?>">Back to Dashboard</a></div>
</header>

<?php if ($roles === []): ?>
    <section class="card admin-empty-state"><h2>No roles available</h2><p>Create an Admin role before configuring Dashboard defaults.</p></section>
<?php else: ?>
    <section class="card">
        <form method="get" action="<?= $e($roleDefaultsUrl) ?>" class="form-grid">
            <label for="dashboard-role">Role</label>
            <div class="actions">
                <select id="dashboard-role" name="role_id">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $e((string) $role->id) ?>"<?= $selectedRole?->id === $role->id ? ' selected' : '' ?>><?= $e($role->label) ?> (<?= $e($role->code) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="button--secondary">Load role</button>
            </div>
        </form>
    </section>

    <?php if ($selectedRole !== null): ?>
        <details class="card dashboard-personalisation" open data-dashboard-personalisation>
            <summary><?= $e($selectedRole->label) ?> Dashboard defaults</summary>
            <p class="muted">Only widgets available to your account are shown. Defaults never grant widget access. Widgets introduced later remain visible by default.</p>
            <?php if ($availableWidgets === []): ?>
                <div class="admin-empty-state"><h2>No configurable widgets</h2><p>No Dashboard widgets are available to your account.</p></div>
            <?php else: ?>
                <form method="post" action="<?= $e($saveRoleDefaultsUrl) ?>" data-dashboard-personalisation-form>
                    <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                    <input type="hidden" name="role_id" value="<?= $e((string) $selectedRole->id) ?>">
                    <ol class="dashboard-personalisation__list" data-dashboard-widget-order>
                        <?php foreach ($availableWidgets as $widget): ?>
                            <?php $visible = !in_array($widget->code, $hiddenWidgetCodes, true); ?>
                            <li class="dashboard-personalisation__item" data-dashboard-order-item="<?= $e($widget->code) ?>">
                                <input type="hidden" name="known_widgets[]" value="<?= $e($widget->code) ?>">
                                <input type="hidden" name="widget_order[]" value="<?= $e($widget->code) ?>" data-dashboard-order-input>
                                <label class="checkbox dashboard-personalisation__visibility">
                                    <input type="checkbox" name="visible_widgets[]" value="<?= $e($widget->code) ?>"<?= $visible ? ' checked' : '' ?> data-dashboard-visibility>
                                    <span><?= $e($widget->title) ?></span>
                                </label>
                                <div class="dashboard-personalisation__moves" aria-label="Move <?= $e($widget->title) ?>">
                                    <button type="button" class="button--secondary dashboard-move" data-dashboard-move="up" aria-label="Move <?= $e($widget->title) ?> up">↑</button>
                                    <button type="button" class="button--secondary dashboard-move" data-dashboard-move="down" aria-label="Move <?= $e($widget->title) ?> down">↓</button>
                                    <button type="button" class="button--secondary dashboard-drag-handle" draggable="true" data-dashboard-drag-handle aria-label="Drag <?= $e($widget->title) ?> to reorder">Drag</button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                    <p class="visually-hidden" role="status" aria-live="polite" data-dashboard-order-status></p>
                    <div class="actions dashboard-personalisation__actions"><button type="submit">Save role defaults</button></div>
                </form>
            <?php endif; ?>
            <?php if ($roleDefaultConfigured): ?>
                <form method="post" action="<?= $e($resetRoleDefaultsUrl) ?>" class="dashboard-personalisation__reset">
                    <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                    <input type="hidden" name="role_id" value="<?= $e((string) $selectedRole->id) ?>">
                    <button type="submit" class="button--secondary">Reset role defaults</button>
                </form>
            <?php endif; ?>
        </details>
    <?php endif; ?>
<?php endif; ?>










