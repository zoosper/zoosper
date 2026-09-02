<?php

declare(strict_types=1);

it('refines the feature-owned Announcements workspace without changing lifecycle ownership', function (): void {
    $root = dirname(__DIR__, 2);
    $view = (string) file_get_contents($root . '/resources/views/announcements/index.php');
    $css = (string) file_get_contents($root . '/resources/assets/css/announcement-workspace.css');
    $routes = require $root . '/config/admin_routes.php';
    $controller = (string) file_get_contents($root . '/src/Controller/AnnouncementAdminController.php');
    $assets = require $root . '/config/admin_assets.php';

    expect($view)->toContain('data-announcement-workspace')->toContain('System / Announcements')->toContain('announcement-workspace__layout')->toContain('announcement-history__table')->toContain('announcement-status--published')->toContain('announcement-status--draft')->toContain('announcement-status--archived')->toContain('name="_csrf_token"')->not->toContain(' style=')
        ->and($css)->toContain('Phase 12K: feature-owned Global Announcements workspace refinement.')->not->toContain('.admin-topbar__title')->not->toContain(':has(')->toContain('grid-template-columns: minmax(19rem, 25rem) minmax(0, 1fr)')->toContain('@media (prefers-contrast: more)')
        ->and($assets['assets']['zoosper-global-announcements-workspace-style']['screens'] ?? [])->toBe(['announcements'])
        ->and($controller)->toContain("title: '',")->toContain("template: 'zoosper-global-announcements::announcements/index'")->toContain('$this->announcements->all()')->toContain('$this->announcements->acknowledgmentCounts()')->toContain("['draft', 'published', 'archived']")
        ->and(array_column($routes, 'permission', 'path')['/admin/announcements'] ?? null)->toBe('settings.manage')
        ->and(array_column($routes, 'method', 'path')['/admin/announcements/save'] ?? null)->toBe('POST')
        ->and(array_column($routes, 'method', 'path')['/admin/announcements/publish'] ?? null)->toBe('POST')
        ->and(array_column($routes, 'method', 'path')['/admin/announcements/unpublish'] ?? null)->toBe('POST')
        ->and(array_column($routes, 'method', 'path')['/admin/announcements/archive'] ?? null)->toBe('POST');
});
