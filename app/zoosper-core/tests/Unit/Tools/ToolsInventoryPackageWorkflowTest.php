<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Tools;

test('package workflow tools are classified as keep ops', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/bin/tools-inventory.php');

    foreach ([
        'ensure-package-testsuites.php',
        'generate-module-composer-manifests.php',
        'normalise-package-testsuites.php',
        'pilot-extract-media-path-repository.php',
        'remove-media-app-compatibility.php',
        'sync-module-autoload.php',
    ] as $tool) {
        expect($source)->toContain("'" . $tool . "'");
    }
});

test('package workflow exact matches are classified before verify migration rule', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/bin/tools-inventory.php');

    $exactPosition = strpos($source, '$opsExact = [');
    $verifyRulePosition = strpos($source, "str_starts_with(\$name, 'verify-')");

    expect($exactPosition)->not->toBeFalse();
    expect($verifyRulePosition)->not->toBeFalse();
    expect($exactPosition)->toBeLessThan($verifyRulePosition);
});
