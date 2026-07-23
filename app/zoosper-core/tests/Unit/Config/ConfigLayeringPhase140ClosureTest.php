<?php

declare(strict_types=1);

it('keeps the Phase 1.40 config layering runtime classes available', function (): void {
    expect(class_exists(Zoosper\Core\Config\ConfigLayerSource::class))->toBeTrue();
    expect(class_exists(Zoosper\Core\Config\ConfigFileLayeredLoader::class))->toBeTrue();
    expect(class_exists(Zoosper\Core\Config\LayeredConfigLoader::class))->toBeTrue();
    expect(class_exists(Zoosper\Core\Config\LayeredConfigResult::class))->toBeTrue();
    expect(class_exists(Zoosper\Admin\Form\AdminConfigLayeredFileLoader::class))->toBeTrue();
});

it('keeps AdminFormConfigAggregator wired to the layered bridge', function (): void {
    $root = dirname(__DIR__, 5);
    $aggregator = $root . '/app/zoosper-admin/src/Form/AdminFormConfigAggregator.php';

    expect($aggregator)->toBeFile();

    $source = (string) file_get_contents($aggregator);

    expect($source)->toContain('AdminConfigLayeredFileLoader');
    expect($source)->toContain('PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED');
    expect($source)->toContain('loadLayeredAdminFormConfigFile');
    expect($source)->not->toMatch('/\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*require\b/');
});

it('keeps Phase 1.40 closure audit tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-config-layering-phase-140-closure.php')->toBeFile();
});
