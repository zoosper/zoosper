<?php

declare(strict_types=1);

/**
 * Durable tool registry audit.
 *
 * Read-only guard that verifies all tools declared in config/durable-tools.php
 * exist and that no registry entry points outside tools/.
 */

$root = dirname(__DIR__);
$registryFile = $root . '/config/durable-tools.php';
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];
$observations = [];
$entries = [];

if (!is_file($registryFile)) {
    $errors[] = 'Missing durable tool registry: config/durable-tools.php';
    $registry = [];
} else {
    $registry = require $registryFile;
    if (!is_array($registry)) {
        $errors[] = 'Durable tool registry must return an array.';
        $registry = [];
    }
}

foreach ($registry as $relative => $metadata) {
    if (!is_string($relative) || $relative === '') {
        $errors[] = 'Durable tool registry contains a non-string or empty key.';
        continue;
    }

    if (!str_starts_with($relative, 'tools/')) {
        $errors[] = 'Durable tool registry entry must live under tools/: ' . $relative;
    }

    if (str_contains($relative, '..')) {
        $errors[] = 'Durable tool registry entry must not contain path traversal: ' . $relative;
    }

    if (!is_file($root . '/' . $relative)) {
        $errors[] = 'Registered durable tool is missing: ' . $relative;
    }

    if (!is_array($metadata) || !isset($metadata['reason']) || trim((string) $metadata['reason']) === '') {
        $errors[] = 'Registered durable tool is missing a non-empty reason: ' . $relative;
    }

    $entries[] = $relative;
}

$observations[] = 'Durable tool entries checked: ' . count($entries);
$observations[] = 'Registry file: config/durable-tools.php';

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
    'entries' => $entries,
];

$report = [];
$report[] = '## Durable Tool Registry Audit';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = '';
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = 'Observations: ' . count($observations);
$report[] = '';
$report[] = '### Entries';
foreach ($entries as $entry) {
    $report[] = '- ' . $entry;
}
$report[] = '';
$report[] = '### Observations';
foreach ($observations as $observation) {
    $report[] = '- ' . $observation;
}
if ($warnings !== []) {
    $report[] = '';
    $report[] = '### Warnings';
    foreach ($warnings as $warning) {
        $report[] = '- ' . $warning;
    }
}
if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportDir . '/durable-tool-registry.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/durable-tool-registry.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
