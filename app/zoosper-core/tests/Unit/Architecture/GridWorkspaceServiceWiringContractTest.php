<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Architecture;

test('Grid workspace integration patches preserve package ownership', function (): void {
    $root = dirname(__DIR__, 5);
    $grid = (string) file_get_contents(
        $root . '/packages/zoosper-admin-grid/config/services.php.patch.md',
    );
    $admin = (string) file_get_contents(
        $root . '/app/zoosper-admin/config/services.php.patch.md',
    );
    $page = (string) file_get_contents(
        $root . '/app/zoosper-page/config/services.php.patch.md',
    );

    expect($grid)->not->toContain('Zoosper\\\\Admin\\\\')
        ->toContain('GridWorkspaceExportAuditorFactory::create');
    expect($admin)->toContain('AdminGridAuditLoggerBridge')
        ->toContain('AuditLoggerInterface');
    expect($page)->toContain('PageGridPageBuilder')
        ->toContain('PageGridAuditedExportCoordinator');
});

test('Page Grid route contract separates view mutation and export', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = (string) file_get_contents(
        $root . '/app/zoosper-page/config/routes.php.patch.md',
    );

    expect($routes)->toContain('GET  /admin/pages')
        ->toContain('POST /admin/pages/grid')
        ->toContain('GET  /admin/pages/export')
        ->not->toContain('/{gridKey}')
        ->not->toContain('/{userId}');
});
