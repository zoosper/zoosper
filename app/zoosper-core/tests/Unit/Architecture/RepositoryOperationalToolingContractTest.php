<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Architecture;

test('interactive session and completed one-shot scripts remain retired', function (): void {
    $basePath = dirname(__DIR__, 5);
    $retired = [
        'collect-and-run.sh',
        'bin/cleanup-legacy-tooling.sh',
        'bin/cleanup-old-root-tests.sh',
        'bin/pest.sh',
    ];

    foreach ($retired as $relativePath) {
        expect($basePath . '/' . $relativePath)
            ->not->toBeFile('Retired repository tooling returned: ' . $relativePath);
    }
});

test('Composer owns the canonical Pest entry points', function (): void {
    $basePath = dirname(__DIR__, 5);
    $composer = json_decode(
        (string) file_get_contents($basePath . '/composer.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );
    $scripts = $composer['scripts'] ?? [];

    expect($scripts['test'] ?? null)->toBe('@php pest');
    expect($scripts['test:unit'] ?? null)->toBe('@php pest --testsuite=Unit');
    expect($scripts['test:feature'] ?? null)->toBe('@php pest --testsuite=Feature');
    expect($scripts['test:coverage'] ?? null)->toBe('@php pest --coverage --min=60');
});










