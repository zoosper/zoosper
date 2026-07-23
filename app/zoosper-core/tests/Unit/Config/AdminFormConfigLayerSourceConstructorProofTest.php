<?php

declare(strict_types=1);

it('keeps ConfigLayerSource constructor proof tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/discover-config-file-layered-loader-contract.php')->toBeFile();
    expect($root . '/tools/prove-admin-form-config-root-overrides.php')->toBeFile();
});

it('documents ConfigLayerSource source path constructor shape', function (): void {
    expect(class_exists(Zoosper\Core\Config\ConfigLayerSource::class))->toBeTrue();

    $reflection = new ReflectionClass(Zoosper\Core\Config\ConfigLayerSource::class);
    $constructor = $reflection->getConstructor();

    expect($constructor)->not->toBeNull();
    expect($constructor->getNumberOfParameters())->toBe(2);
    expect($constructor->getParameters()[0]->getName())->toBe('source');
    expect($constructor->getParameters()[1]->getName())->toBe('path');
});
