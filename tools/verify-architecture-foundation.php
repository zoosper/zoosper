<?php

declare(strict_types=1);

/**
 * Architecture foundation verification runner.
 *
 * Runs the permanent architecture guard tools and writes one combined report.
 * This runner is read-only and intentionally does not run Composer or Pest.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$startedAt = microtime(true);

$guards = [
    'core feature coupling' => 'tools/audit-core-feature-coupling.php',
    'core feature decoupling closure' => 'tools/audit-core-feature-decoupling-closure.php',
    'site lookup boundary regression' => 'tools/audit-site-lookup-boundary-regression.php',
    'site lookup service binding' => 'tools/audit-site-lookup-service-binding.php',
    'site lookup service binding regression' => 'tools/audit-site-lookup-service-binding-regression.php',
    'architecture foundation gates' => 'tools/audit-architecture-foundation-gates.php',
];

$results = [];
$errors = [];
$warnings = [];

foreach ($guards as $label => $relativeScript) {
    $script = $root . '/' . $relativeScript;
    if (!is_file($script)) {
        $errors[] = 'Missing guard script: ' . $relativeScript;
        $results[] = [
            'label' => $label,
            'script' => $relativeScript,
            'exitCode' => 127,
            'status' => 'missing',
            'output' => '',
        ];
        continue;
    }

    $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' 2>&1';
    $output = [];
    $exitCode = 0;
    exec($command, $output, $exitCode);

    $results[] = [
        'label' => $label,
        'script' => $relativeScript,
        'exitCode' => $exitCode,
        'status' => $exitCode === 0 ? 'pass' : 'fail',
        'output' => implode("\n", $output),
    ];

    if ($exitCode !== 0) {
        $errors[] = 'Guard failed: ' . $label . ' (' . $relativeScript . ') exit ' . $exitCode;
    }
}

$durationMs = (int) round((microtime(true) - $startedAt) * 1000);

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'durationMs' => $durationMs,
    'errors' => $errors,
    'warnings' => $warnings,
    'results' => $results,
];

$report = [];
$report[] = '## Architecture Foundation Verification';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = 'DurationMs: ' . $durationMs;
$report[] = '';
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = 'Guards: ' . count($results);
$report[] = '';
$report[] = '### Guard results';
foreach ($results as $result) {
    $report[] = '- ' . $result['label'] . ': ' . strtoupper($result['status']) . ' (' . $result['script'] . ', exit ' . $result['exitCode'] . ')';
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

$report[] = '';
$report[] = '### Notes';
$report[] = '- This runner is read-only.';
$report[] = '- It runs architecture audit tools only.';
$report[] = '- Run Composer and Pest separately as release gates.';

file_put_contents($reportDir . '/architecture-foundation-verification.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/architecture-foundation-verification.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
