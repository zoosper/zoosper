<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Grid;

use ReflectionClass;
use Zoosper\Admin\Grid\AdminGridAuditLoggerBridge;
use Zoosper\AdminGrid\GridWorkspaceAuditLoggerInterface;

test('Admin provides the host audit bridge without leaking Admin into Grid', function (): void {
    $reflection = new ReflectionClass(AdminGridAuditLoggerBridge::class);

    expect($reflection->implementsInterface(GridWorkspaceAuditLoggerInterface::class))->toBeTrue();
    expect((string) file_get_contents(
        dirname(__DIR__, 5) . '/packages/zoosper-admin-grid/src/GridWorkspaceExportAuditLoggerAdapter.php',
    ))->not->toContain('Zoosper\Admin\\');
});










