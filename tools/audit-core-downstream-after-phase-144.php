<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$coreSrc = $root . '/app/zoosper-core/src';
$errors = 0;
$violations = [];
$report = [];

$namespaces = [
    'Zoosper\\Page\\',
    'Zoosper\\Site\\',
    'Zoosper\\Admin\\',
    'Zoosper\\Auth\\',
    'Zoosper\\Theme\\',
    'Zoosper\\Api\\',
    'Zoosper\\Media\\',
    'Zoosper\\Mail\\',
    'Zoosper\\TwoFactor\\',
    'Zoosper\\UrlRewrite\\',
];

$report[] = '## Core Downstream Dependency Snapshot After Phase 1.44';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Mode: snapshot only; remaining violations are expected until runtime cutover phases remove them.';
$report[] = '';

if (!is_dir($coreSrc)) {
    $report[] = 'ERROR core source directory missing: ' . $coreSrc;
    $errors++;
} else {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($coreSrc, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        $path = $file->getPathname();
        if (!str_ends_with($path, '.php')) {
            continue;
        }

        $relative = str_replace($root . '/', '', $path);
        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];
        foreach ($lines as $lineNo => $line) {
            foreach ($namespaces as $namespace) {
                if (str_contains($line, $namespace)) {
                    $violations[] = [
                        'file' => $relative,
                        'line' => $lineNo + 1,
                        'namespace' => $namespace,
                        'source' => trim($line),
                    ];
                }
            }
        }
    }
}

$report[] = 'Remaining downstream references: ' . count($violations);
$report[] = 'Runtime changed: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/core-downstream-after-phase-144.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/core-downstream-after-phase-144.json', json_encode($violations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
file_put_contents($reportDir . '/core-downstream-after-phase-144.log', "CORE_DOWNSTREAM_AFTER_PHASE_144_REMAINING " . count($violations) . "\nCORE_DOWNSTREAM_AFTER_PHASE_144_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
