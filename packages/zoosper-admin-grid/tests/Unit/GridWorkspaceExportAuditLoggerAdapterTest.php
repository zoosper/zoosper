<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspaceAuditLoggerInterface;
use Zoosper\AdminGrid\GridWorkspaceExportAudit;
use Zoosper\AdminGrid\GridWorkspaceExportAuditLoggerAdapter;
use Zoosper\AdminGrid\GridWorkspaceExportAuditorFactory;
use Zoosper\AdminGrid\NullGridWorkspaceExportAuditor;

test('export audit adapter forwards the completed structured context', function (): void {
    $logger = new class implements GridWorkspaceAuditLoggerInterface {
        public ?string $action = null;
        public array $context = [];
        public function logAction(string $action, array $context = []): void
        {
            $this->action = $action;
            $this->context = $context;
        }
    };
    $adapter = new GridWorkspaceExportAuditLoggerAdapter($logger);
    $adapter->record(new GridWorkspaceExportAudit(
        adminUserId: 10,
        gridKey: 'admin.pages',
        filename: 'pages.csv',
        exportedRows: 27,
        truncated: false,
        filters: ['status' => 'published'],
        visibleColumns: ['id', 'title'],
    ));

    expect($logger->action)->toBe('admin_grid.export');
    expect($logger->context)->toBe([
        'admin_user_id' => 10,
        'grid_key' => 'admin.pages',
        'filename' => 'pages.csv',
        'exported_rows' => 27,
        'truncated' => false,
        'filters' => ['status' => 'published'],
        'visible_columns' => ['id', 'title'],
    ]);
});

test('auditor factory uses an explicit null object when host audit is absent', function (): void {
    expect(GridWorkspaceExportAuditorFactory::create(null))
        ->toBeInstanceOf(NullGridWorkspaceExportAuditor::class);
});











