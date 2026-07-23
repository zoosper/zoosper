<?php

declare(strict_types=1);

it('keeps admin form config aggregator layered wiring ready for functional parity', function (): void {
    $root = dirname(__DIR__, 5);
    $target = $root . '/app/zoosper-admin/src/Form/AdminFormConfigAggregator.php';

    expect(class_exists(Zoosper\Admin\Form\AdminFormConfigAggregator::class))->toBeTrue();
    expect(class_exists(Zoosper\Admin\Form\AdminConfigLayeredFileLoader::class))->toBeTrue();
    expect($target)->toBeFile();

    $source = (string) file_get_contents($target);

    expect($source)->toContain('AdminConfigLayeredFileLoader');
    expect($source)->toContain('PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED');
    expect($source)->toContain('loadLayeredAdminFormConfigFile');
    expect($source)->not->toMatch('/\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*require\b/');
});

it('keeps admin form config aggregator contract discovery tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/discover-admin-form-config-aggregator-contract.php')->toBeFile();
    expect($root . '/tools/audit-admin-form-config-aggregator-functional-parity-readiness.php')->toBeFile();
});
