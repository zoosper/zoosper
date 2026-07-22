<?php

declare(strict_types=1);

/**
 * Audit legacy verify migration status ledger consistency.
 *
 * This command is read-only. It does not delete or rewrite source files.
 */

$root = dirname(__DIR__);
$statusPath = $root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'development' . DIRECTORY_SEPARATOR . 'legacy-verify-migration-status.md';
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_file($statusPath)) {
    fwrite(STDERR, 'Migration status ledger not found: ' . $statusPath . PHP_EOL);
    exit(1);
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$contents = (string) file_get_contents($statusPath);
$entries = parseStatusTable($contents);

if ($entries === []) {
    fwrite(STDERR, 'No legacy verify migration status entries found.' . PHP_EOL);
    exit(1);
}

$validStatuses = ['source-owned', 'migrated'];
$errors = [];
$rows = [];

foreach ($entries as $entry) {
    $script = $entry['script'];
    $status = $entry['status'];
    $scriptPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $script);
    $exists = is_file($scriptPath);

    if (! in_array($status, $validStatuses, true)) {
        $errors[] = 'Invalid status for ' . $script . ': ' . $status;
    }

    if ($status === 'source-owned' && ! $exists) {
        $errors[] = 'Source-owned script is missing: ' . $script;
    }

    $rows[] = [
        'script' => $script,
        'status' => $status,
        'exists' => $exists,
    ];
}

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'legacy-verify-migration-status.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'legacy-verify-migration-status.log';

$report = [];
$report[] = '# Legacy Verify Migration Status Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Entries: ' . count($rows);
$report[] = 'Errors: ' . count($errors);
$report[] = '';

foreach ($rows as $row) {
    $report[] = '## ' . $row['script'];
    $report[] = '- Status: ' . $row['status'];
    $report[] = '- Exists: ' . ($row['exists'] ? 'yes' : 'no');
    $report[] = '';
}

if ($errors !== []) {
    $report[] = '## Errors';
    $report[] = '';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Legacy verify migration status written to: ' . $txtPath;
$log[] = 'STATUS_ENTRIES ' . count($rows);
$log[] = 'STATUS_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;

file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

if ($errors !== []) {
    exit(1);
}

/**
 * @return list<array{script: string, status: string}>
 */
function parseStatusTable(string $contents): array
{
    $entries = [];
    foreach (preg_split('/\R/', $contents) ?: [] as $line) {
        $line = trim($line);

        if (! str_starts_with($line, '| `tools/verify-')) {
            continue;
        }

        $columns = array_map('trim', explode('|', trim($line, '|')));
        if (count($columns) < 2) {
            continue;
        }

        $script = trim($columns[0], '` ');
        $status = trim($columns[1], '` ');

        $entries[] = [
            'script' => $script,
            'status' => $status,
        ];
    }

    return $entries;
}
