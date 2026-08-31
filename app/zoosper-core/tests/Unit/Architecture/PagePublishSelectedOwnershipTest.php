<?php

declare(strict_types=1);

it('keeps Page publication orchestration in the Page module', function (): void {
    $root = dirname(__DIR__, 5);
    $executor = file_get_contents($root . '/app/zoosper-page/src/Admin/BulkAction/PagePublishSelectedExecutor.php');
    expect($executor)->not->toBeFalse();
    expect($executor)->toContain('PageRepository');
    expect($executor)->toContain('PagePublishSideEffectsInterface');
    expect($executor)->not->toContain('$_POST');
    expect($executor)->not->toContain('$_SESSION');
    expect($executor)->not->toContain('header(');
});










