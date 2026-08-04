<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Page\Admin\PageGridBulkActions;

it('declares the protected Page publish action separately from the browser manifest', function (): void {
    $browserIds = array_map(static fn ($definition): string => $definition->id, PageGridBulkActions::definitions());
    $server = PageGridBulkActions::serverDefinitions();

    expect($browserIds)->toBe(['export.selected']);
    expect($server)->toHaveCount(1);
    expect($server[0]->id)->toBe('page.publish');
    expect($server[0]->requiredPermission)->toBe('page.manage');
    expect($server[0]->maximumSelection)->toBe(100);
    expect($server[0]->auditRequired)->toBeTrue();
});

it('composes production registries with mandatory event and audit services', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents(
        $root . '/app/zoosper-page/src/Admin/BulkAction/PageBulkActionBackend.php',
    );

    expect($source)->not->toBeFalse();
    expect($source)->toContain('EventDispatcherInterface $events');
    expect($source)->toContain('AuditLoggerInterface $audit');
    expect($source)->toContain('PageGridBulkActions::serverDefinitions()');
    expect($source)->toContain('new PagePublishSelectedExecutor(');
    expect($source)->toContain('new PagePublishSideEffects($events, $audit)');
    expect($source)->not->toContain('?EventDispatcherInterface');
    expect($source)->not->toContain('?AuditLoggerInterface');
});
