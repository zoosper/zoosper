<?php

declare(strict_types=1);

it('keeps the public module contract Zoosper-only', function (): void {
    $root = dirname(__DIR__, 5);
    foreach ([$root . '/README.md', $root . '/docs/modules.md', $root . '/docs/developer-guide.md'] as $file) {
        $contents = (string) file_get_contents($file);
        expect($contents)->not->toContain('marko-module')
            ->not->toContain('extra.marko.module')
            ->not->toContain('"marko"');
    }
    expect((string) file_get_contents($root . '/docs/modules.md'))->toContain('zoosper-module');
});
