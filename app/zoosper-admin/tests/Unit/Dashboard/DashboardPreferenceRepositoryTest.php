<?php

declare(strict_types=1);

use Zoosper\Admin\Dashboard\DashboardPreference;
use Zoosper\Admin\Dashboard\DashboardPreferenceRepository;

function dashboardPreferenceDatabase(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE admin_dashboard_preferences (id INTEGER PRIMARY KEY AUTOINCREMENT, admin_user_id INTEGER NOT NULL, hidden_widget_codes_json TEXT NOT NULL, widget_order_json TEXT NOT NULL, updated_at TEXT NOT NULL)');
    $pdo->exec('CREATE UNIQUE INDEX idx_admin_dashboard_preferences_user ON admin_dashboard_preferences(admin_user_id)');

    return $pdo;
}

it('persists updates and isolates Dashboard preferences by current admin user', function (): void {
    $pdo = dashboardPreferenceDatabase();
    $repository = new DashboardPreferenceRepository($pdo);
    $repository->saveForUser(10, new DashboardPreference(['one'], ['two', 'one']));
    $repository->saveForUser(11, new DashboardPreference([], ['other']));
    $repository->saveForUser(10, new DashboardPreference(['two'], ['one', 'two']));

    expect($repository->findForUser(10)?->hiddenWidgetCodes)->toBe(['two'])
        ->and($repository->findForUser(10)?->widgetOrder)->toBe(['one', 'two'])
        ->and($repository->findForUser(11)?->widgetOrder)->toBe(['other'])
        ->and((int) $pdo->query('SELECT COUNT(*) FROM admin_dashboard_preferences')->fetchColumn())->toBe(2);
});

it('clears only the requested user and treats malformed stored state as defaults', function (): void {
    $pdo = dashboardPreferenceDatabase();
    $repository = new DashboardPreferenceRepository($pdo);
    $repository->saveForUser(10, new DashboardPreference([], ['one']));
    $repository->saveForUser(11, new DashboardPreference([], ['other']));
    $repository->clearForUser(10);
    $pdo->exec("UPDATE admin_dashboard_preferences SET widget_order_json = '{bad' WHERE admin_user_id = 11");

    expect($repository->findForUser(10))->toBeNull()
        ->and($repository->findForUser(11))->toBeNull();
});
