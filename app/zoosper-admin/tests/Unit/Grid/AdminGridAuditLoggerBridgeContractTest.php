<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Grid;

use ReflectionClass;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Admin\Grid\AdminGridAuditLoggerBridge;
use Zoosper\AdminGrid\GridWorkspaceAuditLoggerInterface;

test('Admin provides the host audit bridge without leaking Admin into Grid', function (): void {
    $reflection = new ReflectionClass(AdminGridAuditLoggerBridge::class);

    expect($reflection->implementsInterface(GridWorkspaceAuditLoggerInterface::class))->toBeTrue();
    expect((string) file_get_contents(
        dirname(__DIR__, 5) . '/packages/zoosper-admin-grid/src/GridWorkspaceExportAuditLoggerAdapter.php',
    ))->not->toContain('Zoosper\Admin\\');
});











test('Admin audit bridge forwards the Grid export context through the canonical audit contract', function (): void {
    $audit = new class implements AuditLoggerInterface {
        /** @var list<array<string, mixed>> */
        public array $events = [];

        public function logAction(
            ?int $actorAdminUserId,
            ?string $actorEmail,
            string $action,
            string $entityType,
            ?string $entityId,
            string $summary,
            array $metadata = [],
        ): void {
            $this->events[] = compact(
                'actorAdminUserId',
                'actorEmail',
                'action',
                'entityType',
                'entityId',
                'summary',
                'metadata',
            );
        }
    };

    (new AdminGridAuditLoggerBridge($audit))->logAction('admin_grid.export', [
        'admin_user_id' => 10,
        'grid_key' => 'admin.pages',
        'filename' => 'pages.csv',
        'exported_rows' => 27,
    ]);

    expect($audit->events)->toBe([[
        'actorAdminUserId' => 10,
        'actorEmail' => null,
        'action' => 'admin_grid.export',
        'entityType' => 'admin_grid',
        'entityId' => 'admin.pages',
        'summary' => 'Exported Admin Grid workspace data.',
        'metadata' => [
            'admin_user_id' => 10,
            'grid_key' => 'admin.pages',
            'filename' => 'pages.csv',
            'exported_rows' => 27,
        ],
    ]]);
});