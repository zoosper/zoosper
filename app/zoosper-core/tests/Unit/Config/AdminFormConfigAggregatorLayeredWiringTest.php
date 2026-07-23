<?php

declare(strict_types=1);

it('wires AdminFormConfigAggregator to the admin config layered runtime bridge', function (): void {
    $root = dirname(__DIR__, 5);
    $target = $root . '/app/zoosper-admin/src/Form/AdminFormConfigAggregator.php';

    expect($target)->toBeFile();

    $source = (string) file_get_contents($target);

    expect($source)->toContain('AdminConfigLayeredFileLoader');
    expect($source)->toContain('PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED');
    expect($source)->toContain('loadLayeredAdminFormConfigFile');
    expect($source)->not->toMatch('/\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*require\b/');
});

it('keeps aggregator layered wiring tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/apply-admin-form-config-aggregator-layered-loader.php')->toBeFile();
    expect($root . '/tools/audit-admin-form-config-aggregator-layered-wiring.php')->toBeFile();
});
