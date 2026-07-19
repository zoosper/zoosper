<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$packagePath = $basePath . '/packages/zoosper-media';

print "Zoosper media standalone package audit\n";
print "======================================\n\n";

$composer = readJson($packagePath . '/composer.json');

$checks = [
    'package directory exists' => is_dir($packagePath),
    'composer.json exists' => is_file($packagePath . '/composer.json'),
    'composer name is zoosper/media' => ($composer['name'] ?? null) === 'zoosper/media',
    'composer type is zoosper-module' => ($composer['type'] ?? null) === 'zoosper-module',
    'requires php 8.5' => isset($composer['require']['php']) && str_contains((string) $composer['require']['php'], '8.5'),
    'requires ext-pdo' => isset($composer['require']['ext-pdo']),
    'declares zoosper core dependency' => isset($composer['require']['zoosper/core']),
    'declares psr-4 source autoload' => ($composer['autoload']['psr-4']['Zoosper\\Media\\'] ?? null) === 'src/',
    'declares psr-4 test autoload' => ($composer['autoload-dev']['psr-4']['Zoosper\\Media\\Tests\\'] ?? null) === 'tests/',
    'declares module extra metadata' => ($composer['extra']['zoosper']['module'] ?? null) === 'module.php',
    'module.php exists' => is_file($packagePath . '/module.php'),
    'config directory exists' => is_dir($packagePath . '/config'),
    'src directory exists' => is_dir($packagePath . '/src'),
    'tests directory exists' => is_dir($packagePath . '/tests/Unit'),
    'standalone phpunit config exists' => is_file($packagePath . '/phpunit.xml.dist'),
    'README exists' => is_file($packagePath . '/README.md'),
    'package gitignore exists' => is_file($packagePath . '/.gitignore'),
    'workflow template exists' => is_file($packagePath . '/.github/workflows/tests.yml'),
];

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);

/** @return array<string, mixed> */
function readJson(string $file): array
{
    if (!is_file($file)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($file), true);

    return is_array($decoded) ? $decoded : [];
}
