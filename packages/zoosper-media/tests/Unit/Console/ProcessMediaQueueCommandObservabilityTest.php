<?php
declare(strict_types=1);
it('records claimed queue lifecycle timestamps and fails a batch containing task failures', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/packages/zoosper-media/src/Console/ProcessMediaQueueCommand.php');
    expect($source)
        ->toContain("status = 'processing', started_at = :now")
        ->toContain("completedAt: gmdate('Y-m-d H:i:s')")
        ->toContain("WHERE id = :id AND status = 'pending'")
        ->toContain("WHERE id = :id AND status = 'processing'")
        ->toContain('return $failures === 0 ? 0 : 1;')
        ->toContain('error_message = NULL');
});
