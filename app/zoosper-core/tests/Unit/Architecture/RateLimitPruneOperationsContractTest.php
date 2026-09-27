<?php

declare(strict_types=1);

it('documents external scheduling without introducing an application scheduler or HTTP trigger', function (): void {
    $root = dirname(__DIR__, 5);
    $guide = (string) file_get_contents($root . '/docs/operations/rate-limit-pruning.md');

    expect($guide)
        ->toContain('/usr/bin/php8.5 bin/zoosper rate-limit:prune')
        ->toContain('flock -n')
        ->toContain('Type=oneshot')
        ->toContain('Persistent=true')
        ->toContain('Keep the command inaccessible from HTTP routes.')
        ->toContain('Zoosper does not install or enable host scheduler configuration.')
        ->toContain('The current command intentionally has no batch-size option.');
});
