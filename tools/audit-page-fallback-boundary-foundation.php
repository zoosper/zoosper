<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$warnings = [];
$reportDir = $root . '/var/reports';

$files = [
    'app/zoosper-core/src/Routing/FallbackHandlerInterface.php',
    'app/zoosper-core/src/Routing/NullFallbackHandler.php',
    'app/zoosper-page/src/Routing/PageFallbackHandler.php',
    'app/zoosper-core/tests/Unit/Architecture/PageFallbackHandlerBoundaryFoundationTest.php',
    'docs/development/page-fallback-handler-boundary-foundation.md',
    'docs/roadmap/roadmap-status-fragment-phase-1.68a-l.md',
];

foreach ($files as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing expected file: ' . $file;
    }
}

$coreFiles = [
    'app/zoosper-core/src/Routing/FallbackHandlerInterface.php',
    'app/zoosper-core/src/Routing/NullFallbackHandler.php',
];
foreach ($coreFiles as $file) {
    $source = is_file($root . '/' . $file) ? (string) file_get_contents($root . '/' . $file) : '';
    if (str_contains($source, 'Zoosper\\Page\\')) {
        $errors[] = 'Core fallback boundary file imports Page module namespace: ' . $file;
    }
}

$pageAdapter = $root . '/app/zoosper-page/src/Routing/PageFallbackHandler.php';
if (is_file($pageAdapter)) {
    $source = (string) file_get_contents($pageAdapter);
    if (!str_contains($source, 'FallbackHandlerInterface')) {
        $errors[] = 'Page fallback adapter does not implement the core fallback boundary.';
    }
}

$applicationFactory = $root . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php';
if (is_file($applicationFactory)) {
    $source = (string) file_get_contents($applicationFactory);
    if (str_contains($source, 'Zoosper\\Page\\Controller\\PageController')) {
        $warnings[] = 'ApplicationFactory still imports PageController. This is expected until the Phase 1.68 runtime cutover.';
    }
}

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$lines = [
    '## Page Fallback Handler Boundary Foundation Audit',
    '',
    'Generated: ' . gmdate('c'),
    '',
    'Errors: ' . count($errors),
    'Warnings: ' . count($warnings),
    '',
    '### Errors',
];
foreach ($errors as $error) {
    $lines[] = '- ' . $error;
}
$lines[] = '';
$lines[] = '### Warnings';
foreach ($warnings as $warning) {
    $lines[] = '- ' . $warning;
}

file_put_contents($reportDir . '/page-fallback-boundary-foundation.txt', implode("\n", $lines) . "\n");
file_put_contents($reportDir . '/page-fallback-boundary-foundation.json', json_encode([
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $lines) . "\n";
exit($errors === [] ? 0 : 1);
