<?php

declare(strict_types=1);

it('keeps admin form config runtime migration tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/apply-admin-form-config-layered-loader.php')->toBeFile();
    expect($root . '/tools/audit-admin-form-config-runtime-migration.php')->toBeFile();
});

it('ensures migrated admin config loaders reference the layered loader', function (): void {
    $root = dirname(__DIR__, 5);
    $targets = [
        $root . '/app/zoosper-admin/src/Form/AdminFormConfigAggregator.php',
        $root . '/app/zoosper-admin/src/Form/AdminFormUiConfigLoader.php',
    ];

    $existingTargets = array_values(array_filter($targets, static fn (string $file): bool => is_file($file)));

    expect($existingTargets)->not->toBeEmpty();

    foreach ($existingTargets as $target) {
        $source = (string) file_get_contents($target);

        if (str_contains($source, 'PHASE_140DF_')) {
            expect($source)->toContain('ConfigFileLayeredLoader');
        }
    }
});
