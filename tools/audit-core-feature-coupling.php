<?php

declare(strict_types=1);

/**
 * Core feature-coupling audit.
 *
 * Read-only scanner for direct references from zoosper-core runtime source into
 * feature/module namespaces. This prepares future decoupling work without
 * changing runtime code in this phase.
 */

$root = dirname(__DIR__);
$strict = in_array('--strict', $argv, true);
$reportDir = $root . '/var/reports';
$coreSrc = $root . '/app/zoosper-core/src';

$forbiddenNamespaces = [
    'Zoosper\\Page\\' => 'Page module',
    'Zoosper\\Site\\' => 'Site module',
    'Zoosper\\Auth\\' => 'Auth module',
    'Zoosper\\Theme\\' => 'Theme module',
    'Zoosper\\Media\\' => 'Media module',
    'Zoosper\\Admin\\' => 'Admin module',
    'Zoosper\\Api\\' => 'API module',
];

$allowedPatterns = [
    // Keep this list intentionally tiny. Add explicit exceptions only when a
    // dependency is proven to be a deliberate contract boundary rather than
    // a feature-module implementation dependency.
];

$violations = [];
$scannedFiles = 0;

if (is_dir($coreSrc)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($coreSrc, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $scannedFiles++;
        $path = $file->getPathname();
        $relative = str_replace($root . '/', '', $path);
        $lines = file($path, FILE_IGNORE_NEW_LINES) ?: [];

        foreach ($lines as $lineNumber => $line) {
            if (isAllowed($relative, $line, $allowedPatterns)) {
                continue;
            }

            foreach ($forbiddenNamespaces as $needle => $label) {
                if (str_contains($line, $needle)) {
                    $violations[] = [
                        'file' => $relative,
                        'line' => $lineNumber + 1,
                        'module' => $label,
                        'needle' => $needle,
                        'source' => trim($line),
                    ];
                }
            }
        }
    }
}

$grouped = [];
foreach ($violations as $violation) {
    $grouped[$violation['module']][] = $violation;
}
ksort($grouped);

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'mode' => $strict ? 'strict' : 'report',
    'scannedFiles' => $scannedFiles,
    'violationCount' => count($violations),
    'violationsByModule' => array_map('count', $grouped),
    'violations' => $violations,
];

$report = [];
$report[] = '## Core Feature Coupling Audit';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = 'Mode: ' . $payload['mode'];
$report[] = '';
$report[] = 'Scanned core source files: ' . $scannedFiles;
$report[] = 'Forbidden references found: ' . count($violations);
$report[] = '';
$report[] = '### Violations by module';
if ($grouped === []) {
    $report[] = '- none';
} else {
    foreach ($grouped as $module => $items) {
        $report[] = '- ' . $module . ': ' . count($items);
    }
}

if ($violations !== []) {
    $report[] = '';
    $report[] = '### Violations';
    foreach ($violations as $violation) {
        $report[] = '- ' . $violation['file'] . ':' . $violation['line'] . ' [' . $violation['module'] . ']';
        $report[] = '  - ' . $violation['source'];
    }
}

file_put_contents($reportDir . '/core-feature-coupling.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/core-feature-coupling.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($strict && $violations !== [] ? 1 : 0);

/**
 * @param list<string> $allowedPatterns
 */
function isAllowed(string $relative, string $line, array $allowedPatterns): bool
{
    foreach ($allowedPatterns as $pattern) {
        if (preg_match($pattern, $relative . ' ' . $line) === 1) {
            return true;
        }
    }

    return false;
}
