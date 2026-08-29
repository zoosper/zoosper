<?php

declare(strict_types=1);

use Zoosper\Auth\Dashboard\AdminUserCountDashboardWidgetContributor;
use Zoosper\Auth\Repository\AdminUserRepository;

it('contributes the current active Admin user count without exposing account data', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE admin_users (id INTEGER PRIMARY KEY, email TEXT, name TEXT, password_hash TEXT, status TEXT, locale TEXT, created_at TEXT, updated_at TEXT)');
    $pdo->exec("INSERT INTO admin_users VALUES (1, 'one@example.test', 'One', 'hash', 'active', NULL, '', ''), (2, 'two@example.test', 'Two', 'hash', 'disabled', NULL, '', ''), (3, 'three@example.test', 'Three', 'hash', 'active', NULL, '', '')");

    $widgets = iterator_to_array((new AdminUserCountDashboardWidgetContributor(new AdminUserRepository($pdo)))->widgets());

    expect($widgets)->toHaveCount(1)
        ->and($widgets[0]->code)->toBe('auth.active-admin-users')
        ->and($widgets[0]->value)->toBe('2')
        ->and($widgets[0]->description)->not->toContain('@example.test');
});
