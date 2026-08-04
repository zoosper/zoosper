<?php

declare(strict_types=1);

it('keeps Page publication side effects decoupled from concrete Admin services', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents(
        $root . '/app/zoosper-page/src/Admin/BulkAction/PagePublishSideEffects.php',
    );

    expect($source)->not->toBeFalse();
    expect($source)->toContain('AuditLoggerInterface');
    expect($source)->toContain('EventDispatcherInterface');
    expect($source)->not->toContain('Zoosper\\Admin\\Audit');
    expect($source)->not->toContain('$_POST');
    expect($source)->not->toContain('$_SESSION');
    expect($source)->not->toContain('?AuditLoggerInterface');
    expect($source)->not->toContain('?EventDispatcherInterface');
});
