<?php

declare(strict_types=1);

namespace Zoosper\GlobalAnnouncements\Tests\Unit;

use Zoosper\Core\Announcement\AdminAnnouncementProviderInterface;
use Zoosper\GlobalAnnouncements\Announcement\AdminAnnouncementRepository;

it('verifies that module config files load correctly', function (): void {
    $moduleDir = dirname(__DIR__, 2);

    $assets = require $moduleDir . '/config/admin_assets.php';
    expect($assets)->toBeArray()
        ->and($assets)->toHaveKey('assets');

    $routes = require $moduleDir . '/config/admin_routes.php';
    expect($routes)->toBeArray()
        ->and(count($routes))->toBeGreaterThanOrEqual(5);

    $menu = require $moduleDir . '/config/admin_menu.php';
    expect($menu)->toBeArray()
        ->and($menu[0]['code'] ?? '')->toBe('announcements');

    $schema = require $moduleDir . '/config/db_schema.php';
    expect($schema)->toBeArray()
        ->and($schema['tables'])->toHaveKeys(['admin_announcements', 'admin_announcement_acknowledgments']);

    $services = require $moduleDir . '/config/services.php';
    expect($services)->toBeArray()
        ->and($services)->toHaveKeys([
            AdminAnnouncementRepository::class,
            AdminAnnouncementProviderInterface::class,
        ]);
});
