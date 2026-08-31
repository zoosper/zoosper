<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Module;

/** @return list<string> */
function firstPartyModuleFiles(string $basePath): array
{
    $files = array_merge(
        glob($basePath . '/app/*/module.php') ?: [],
        glob($basePath . '/packages/*/module.php') ?: [],
    );
    sort($files);

    return $files;
}

test('first-party module files contain only Marko wiring keys', function (): void {
    $basePath = dirname(__DIR__, 5);
    $allowedKeys = ['bindings', 'singletons', 'sequence', 'boot'];
    $moduleFiles = firstPartyModuleFiles($basePath);

    expect($moduleFiles)->not->toBeEmpty();

    foreach ($moduleFiles as $moduleFile) {
        $module = require $moduleFile;

        expect($module)->toBeArray('Module file must return an array: ' . $moduleFile);

        $unsupportedKeys = array_values(array_diff(array_keys($module), $allowedKeys));

        expect($unsupportedKeys)->toBe(
            [],
            'Unsupported module.php metadata in ' . $moduleFile
                . ': ' . implode(', ', $unsupportedKeys),
        );
    }
});

test('first-party module wiring values use the expected shapes', function (): void {
    $basePath = dirname(__DIR__, 5);
    $moduleFiles = firstPartyModuleFiles($basePath);

    expect($moduleFiles)->not->toBeEmpty();

    foreach ($moduleFiles as $moduleFile) {
        $module = require $moduleFile;

        // This assertion is unconditional so an all-empty wiring set is still
        // a valid, non-risky test execution.
        expect($module)->toBeArray('Module file must return an array: ' . $moduleFile);

        foreach (['bindings', 'singletons', 'sequence'] as $arrayKey) {
            if (array_key_exists($arrayKey, $module)) {
                expect($module[$arrayKey])
                    ->toBeArray($arrayKey . ' must be an array in ' . $moduleFile);
            }
        }

        if (array_key_exists('boot', $module)) {
            expect(is_callable($module['boot']))
                ->toBeTrue('boot must be callable in ' . $moduleFile);
        }
    }
});










