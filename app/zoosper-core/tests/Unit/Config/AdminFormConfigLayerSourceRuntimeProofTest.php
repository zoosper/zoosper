<?php

declare(strict_types=1);

it('keeps ConfigLayerSource runtime proof tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/discover-config-file-layered-loader-contract.php')->toBeFile();
    expect($root . '/tools/prove-admin-form-config-root-overrides.php')->toBeFile();
});

it('keeps ConfigLayerSource and ConfigFileLayeredLoader classes available', function (): void {
    expect(class_exists(Zoosper\Core\Config\ConfigFileLayeredLoader::class))->toBeTrue();
    expect(class_exists(Zoosper\Core\Config\ConfigLayerSource::class))->toBeTrue();

    $loaderReflection = new ReflectionClass(Zoosper\Core\Config\ConfigFileLayeredLoader::class);
    expect($loaderReflection->hasMethod('load'))->toBeTrue();
});
