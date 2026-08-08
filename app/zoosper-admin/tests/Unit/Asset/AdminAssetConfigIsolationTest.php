<?php

declare(strict_types=1);

it('isolates module admin asset manifest variables from the registry accumulator', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-admin/src/Asset/AdminAssetRegistry.php');

    expect($source)->toContain('$config = $this->loadConfig($file)')
        ->toContain('static fn (string $manifest): mixed => require $manifest')
        ->not->toContain('$config = require $file');
});
