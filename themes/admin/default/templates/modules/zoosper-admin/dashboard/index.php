<?php
/**
 * @var callable $e
 * @var list<Zoosper\AdminDashboard\DashboardWidget> $availableWidgets
 * @var list<Zoosper\AdminDashboard\DashboardWidget> $widgets
 * @var list<string> $hiddenWidgetCodes
 * @var int $widgetFailureCount
 * @var bool $dashboardCustomised
 * @var string $csrfToken
 * @var string $personalisationUrl
 * @var string $resetPersonalisationUrl
 * @var ?string $roleDefaultsUrl
 */
$availableWidgets = $availableWidgets ?? [];
$widgets = $widgets ?? [];
$hiddenWidgetCodes = $hiddenWidgetCodes ?? [];
$widgetFailureCount = $widgetFailureCount ?? 0;
$dashboardCustomised = $dashboardCustomised ?? false;
?>
<header class="page-header dashboard-hero">
    <div class="page-header__copy">
        <p class="page-header__eyebrow">Admin workspace</p>
        <h1>Dashboard</h1>
        <p class="muted">Current insights from the enabled modules available to your account.</p>
    </div>
    <?php if (is_string($roleDefaultsUrl ?? null)): ?>
        <div class="actions"><a class="button button--secondary" href="<?= $e($roleDefaultsUrl) ?>">Manage role defaults</a></div>
    <?php endif; ?>
</header>

<?php if ($widgetFailureCount > 0): ?>
    <div class="alert alert--warning" role="status">
        Some Dashboard information is temporarily unavailable. Other widgets remain available.
    </div>
<?php endif; ?>

<?php if ($availableWidgets !== []): ?>
    <details class="card dashboard-personalisation" data-dashboard-personalisation>
        <summary><span>Customise dashboard</span><span aria-hidden="true">⚙</span></summary>
        <div class="dashboard-personalisation__body">
        <p class="muted">Choose visible widgets and arrange them for your account. Your permissions still control which widgets are available.</p>
        <form method="post" action="<?= $e($personalisationUrl) ?>" data-dashboard-personalisation-form>
            <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
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
            <div class="actions dashboard-personalisation__actions">
                <button type="submit">Save layout</button>
            </div>
        </form>
        <?php if ($dashboardCustomised): ?>
            <form method="post" action="<?= $e($resetPersonalisationUrl) ?>" class="dashboard-personalisation__reset">
                <input type="hidden" name="_csrf_token" value="<?= $e($csrfToken) ?>">
                <button type="submit" class="button--secondary">Reset to defaults</button>
            </form>
        <?php endif; ?>
        </div>
    </details>
<?php endif; ?>

<section class="dashboard-widgets" aria-labelledby="dashboard-widgets-title">
    <header class="dashboard-section-header">
        <div>
            <p class="page-header__eyebrow">Overview</p>
            <h2 id="dashboard-widgets-title">Dashboard insights</h2>
        </div>
        <p class="muted"><span data-dashboard-visible-count><?= $e((string) count($widgets)) ?></span> visible</p>
    </header>
    <?php if ($availableWidgets === []): ?>
        <div class="admin-empty-state">
            <h3>No Dashboard insights available</h3>
            <p>Enabled modules have not provided any widgets available to your account.</p>
        </div>
    <?php else: ?>
        <div class="dashboard-widget-grid" data-dashboard-widget-grid>
            <?php foreach ($availableWidgets as $widget): ?>
                <?php $visible = !in_array($widget->code, $hiddenWidgetCodes, true); ?>
                <article class="card admin-stat dashboard-widget" tabindex="0" data-dashboard-widget="<?= $e($widget->code) ?>"<?= $visible ? '' : ' hidden' ?>>
                    <button type="button" class="button--secondary dashboard-widget__drag" draggable="true" data-dashboard-card-drag aria-label="Drag <?= $e($widget->title) ?> to reorder">Drag</button>
                    <p class="admin-stat__label"><?= $e($widget->title) ?></p>
                    <p class="admin-stat__value"><?= $e($widget->value) ?></p>
                    <p class="muted"><?= $e($widget->description) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <div class="admin-empty-state" data-dashboard-hidden-empty<?= $widgets === [] ? '' : ' hidden' ?>>
            <h3>All Dashboard insights are hidden</h3>
            <p>Open Customise dashboard to make widgets visible again.</p>
        </div>
    <?php endif; ?>
</section>
