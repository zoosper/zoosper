<?php

declare(strict_types=1);

it('keeps exact runtime admin config proof tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/discover-config-file-layered-loader-contract.php')->toBeFile();
    expect($root . '/tools/prove-admin-form-config-root-overrides.php')->toBeFile();
});

it('keeps config file layered loader contract visible', function (): void {
    expect(class_exists(Zoosper\Core\Config\ConfigFileLayeredLoader::class))->toBeTrue();

    $reflection = new ReflectionClass(Zoosper\Core\Config\ConfigFileLayeredLoader::class);

    expect($reflection->hasMethod('load'))->toBeTrue();
    expect($reflection->getMethod('load')->isPublic())->toBeTrue();
});
