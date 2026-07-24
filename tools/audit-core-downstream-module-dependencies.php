<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$coreSrc = $root . '/app/zoosper-core/src';
$violations = [];
$errors = 0;
$report = [];

$report[] = '## Core Downstream Module Dependency Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Scope: app/zoosper-core/src';
$report[] = 'Mode: report-only';
$report[] = '';

$downstreamNamespaces = [
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

if (!is_dir($coreSrc)) {
    $report[] = 'ERROR: core source directory missing: ' . $coreSrc;
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
            foreach ($downstreamNamespaces as $namespace) {
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

$grouped = [];
foreach ($violations as $violation) {
    $grouped[$violation['file']][] = $violation;
}

$report[] = 'Violations found: ' . count($violations);
$report[] = 'Files with violations: ' . count($grouped);
$report[] = '';

if ($violations === []) {
    $report[] = 'No downstream feature-module imports found in core.';
} else {
    $report[] = '### Violations';
    foreach ($grouped as $file => $items) {
        $report[] = '';
        $report[] = '#### ' . $file;
        foreach ($items as $item) {
            $report[] = '- line ' . $item['line'] . ' | ' . $item['namespace'] . ' | ' . $item['source'];
        }
    }
}

$report[] = '';
$report[] = 'Interpretation: violations are expected at the start of Phase 1.44 and should be reduced by subsequent decoupling sub-phases.';
$report[] = 'Runtime changed: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/core-downstream-module-dependencies.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/core-downstream-module-dependencies.json', json_encode($violations, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
file_put_contents(
    $reportDir . '/core-downstream-module-dependencies.log',
    "CORE_DOWNSTREAM_DEPENDENCY_VIOLATIONS " . count($violations) . "\n" .
    "CORE_DOWNSTREAM_DEPENDENCY_ERRORS {$errors}\n"
);

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
