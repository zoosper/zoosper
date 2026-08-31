<?php

declare(strict_types=1);

use Marko\View\ViewInterface;

it('installs the stable Marko view contract without replacing the current theme runtime', function (): void {
    expect(interface_exists(ViewInterface::class))->toBeTrue();

    $reflection = new ReflectionClass(ViewInterface::class);
    expect($reflection->hasMethod('render'))->toBeTrue();

    $root = dirname(__DIR__, 5);
    $composer = json_decode(
        (string) file_get_contents($root . '/composer.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($composer['require']['marko/view'] ?? null)->toBe('0.8.5')
        ->and($root . '/app/zoosper-theme/src/Template/TemplateRenderer.php')->toBeFile();
});

it('does not prematurely install version-misaligned Marko layout drivers', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode(
        (string) file_get_contents($root . '/composer.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($composer['require'] ?? [])
        ->not->toHaveKey('marko/layout')
        ->not->toHaveKey('marko/view-latte');
});










