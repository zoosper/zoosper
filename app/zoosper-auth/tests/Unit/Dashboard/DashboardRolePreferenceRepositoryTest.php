<?php

declare(strict_types=1);

use Zoosper\Auth\Dashboard\DashboardRolePreferenceRepository;

function roleDashboardPreferencePdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY)');
    $pdo->exec('CREATE TABLE admin_roles (id INTEGER PRIMARY KEY, code TEXT NOT NULL, label TEXT NOT NULL)');
    $pdo->exec('CREATE TABLE admin_user_roles (user_id INTEGER NOT NULL, role_id INTEGER NOT NULL)');
    $pdo->exec('CREATE TABLE admin_role_dashboard_preferences (role_id INTEGER PRIMARY KEY, hidden_widget_codes_json TEXT NOT NULL, widget_order_json TEXT NOT NULL, updated_at TEXT NOT NULL, FOREIGN KEY(role_id) REFERENCES admin_roles(id) ON DELETE CASCADE)');
    $pdo->exec("INSERT INTO admin_users (id) VALUES (1)");
    $pdo->exec("INSERT INTO admin_roles (id, code, label) VALUES (1, 'finance', 'Finance'), (2, 'content_manager', 'Content Manager')");
    $pdo->exec('INSERT INTO admin_user_roles (user_id, role_id) VALUES (1, 1), (1, 2)');

    return $pdo;
}

it('stores role defaults and loads assigned configured roles by deterministic role code', function (): void {
    $pdo = roleDashboardPreferencePdo();
    $repository = new DashboardRolePreferenceRepository($pdo);
    $repository->saveForRole(1, ['content.drafts'], ['finance.total', 'content.drafts']);
    $repository->saveForRole(2, ['finance.total'], ['content.drafts', 'finance.total']);

    $preferences = $repository->findForUser(1);

    expect(array_column($preferences, 'roleCode'))->toBe(['content_manager', 'finance'])
        ->and($repository->findForRole(1)?->hiddenWidgetCodes)->toBe(['content.drafts'])
        ->and($repository->roles())->toHaveCount(2);
});

it('clears defaults independently and cascades stored defaults when a role is deleted', function (): void {
    $pdo = roleDashboardPreferencePdo();
    $repository = new DashboardRolePreferenceRepository($pdo);
    $repository->saveForRole(1, [], ['one']);
    $repository->clearForRole(1);
    expect($repository->findForRole(1))->toBeNull();

    $repository->saveForRole(2, ['one'], ['one']);
    $pdo->exec('DELETE FROM admin_roles WHERE id = 2');
    expect((int) $pdo->query('SELECT COUNT(*) FROM admin_role_dashboard_preferences')->fetchColumn())->toBe(0);
});

it('rejects a role default write for a missing role', function (): void {
    $repository = new DashboardRolePreferenceRepository(roleDashboardPreferencePdo());

    expect(fn () => $repository->saveForRole(999, [], []))->toThrow(RuntimeException::class, 'Dashboard role does not exist.');
});
