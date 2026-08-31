<?php

declare(strict_types=1);

namespace Zoosper\Grid\Tests\Unit;

test('grid package fixtures use the extracted public namespace', function (): void {
    $source = (string) file_get_contents(__DIR__ . '/GridColumnRegistryTest.php');

    expect($source)->toContain('Zoosper\Grid\GridColumn');
    expect($source)->toContain('Zoosper\Grid\GridFilter');
    expect($source)->not->toContain('Zoosper\Core\Grid\');
});

test('grid package is a library with a bounded core dependency', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($manifest['type'] ?? null)->toBe('library');
    expect($manifest['require']['zoosper/pagination'] ?? null)->toBe('dev-dev');
    expect($manifest['require'] ?? [])->not->toHaveKey('zoosper/core');
    expect($manifest['extra'] ?? [])->not->toHaveKey('marko');
});











