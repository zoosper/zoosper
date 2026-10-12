<?php

declare(strict_types=1);

it('records explicit queue lifecycle timestamps and fails a batch containing task failures', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents($root . '/packages/zoosper-media/src/Console/ProcessMediaQueueCommand.php');
    expect($source)->toContain("startedAt: gmdate('Y-m-d H:i:s')")
        ->toContain("completedAt: gmdate('Y-m-d H:i:s')")
        ->toContain('return $failures === 0 ? 0 : 1;')
        ->toContain("error_message = NULL");
});
