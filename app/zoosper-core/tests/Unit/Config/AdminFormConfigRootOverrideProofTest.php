<?php

declare(strict_types=1);

it('keeps admin form config root override proof tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/discover-config-file-layered-loader-contract.php')->toBeFile();
    expect($root . '/tools/prove-admin-form-config-root-overrides.php')->toBeFile();
});

it('keeps admin config runtime migration markers in place', function (): void {
    $root = dirname(__DIR__, 5);
    $targets = [
        $root . '/app/zoosper-admin/src/Form/AdminFormConfigAggregator.php',
        $root . '/app/zoosper-admin/src/Form/AdminFormUiConfigLoader.php',
    ];

    foreach ($targets as $target) {
        expect($target)->toBeFile();
        $source = (string) file_get_contents($target);
        expect($source)->toContain('ConfigFileLayeredLoader');
        expect($source)->toContain('PHASE_140DF_');
    }
});
