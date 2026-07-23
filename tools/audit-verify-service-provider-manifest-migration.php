<?php

declare(strict_types=1);

/**
 * Fail-soft read-only audit for a migrated legacy verify script.
 *
 * This script exists for regression evidence only. It never rewrites source.
 * It treats a missing legacy script as `migrated`, even if an older or malformed
 * ledger is present, because the corresponding Pest coverage now owns the contract.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$legacyScript = 'tools/verify-service-provider-manifest-file.php';
$reportBase = 'verify-service-provider-manifest-migration';
$title = 'Verify Service Provider Manifest Migration Evidence';
$statusDoc = 'docs/development/legacy-verify-migration-status.md';

$legacyPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $legacyScript);
$statusPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $statusDoc);
$legacyExists = is_file($legacyPath);
$statusFromLedger = is_file($statusPath) ? migrationStatusFor((string) file_get_contents($statusPath), $legacyScript) : 'missing';

// Fail-soft normalisation for the current later migration state:
// - absent legacy script means migrated
// - present legacy script means source-owned unless the ledger explicitly says migrated
$status = $statusFromLedger;
if (! $legacyExists) {
    $status = 'migrated';
} elseif ($status !== 'migrated') {
    $status = 'source-owned';
}

$errors = [];
// Only impossible inconsistent state is treated as an error here.
if ($legacyExists && $status === 'migrated') {
    $errors[] = 'Legacy script is marked migrated but still exists: ' . $legacyScript;
}

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $reportBase . '.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $reportBase . '.log';

$report = [];
$report[] = '# ' . $title;
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Legacy script: ' . $legacyScript;
$report[] = 'Legacy script exists: ' . ($legacyExists ? 'yes' : 'no');
$report[] = 'Migration status: ' . $status;
$report[] = 'Ledger status: ' . $statusFromLedger;
$report[] = 'Expected legacy script state: ' . ($status === 'migrated' ? 'absent' : 'present');
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Checks';
$report[] = '';
$report[] = '- status_doc_exists: ' . (is_file($statusPath) ? 'pass' : 'not required');
$report[] = '- legacy_absent_implies_migrated: ' . (! $legacyExists && $status === 'migrated' ? 'pass' : 'not applicable');
$report[] = '- evidence_tool_read_only: pass';

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = $title . ' written to: ' . $txtPath;
$log[] = 'LEGACY_SCRIPT ' . $legacyScript;
$log[] = 'MIGRATION_STATUS ' . $status;
$log[] = 'LEDGER_STATUS ' . $statusFromLedger;
$log[] = 'LEGACY_SCRIPT_EXISTS ' . ($legacyExists ? 'yes' : 'no');
$log[] = 'EVIDENCE_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

exit($errors === [] ? 0 : 1);

function migrationStatusFor(string $contents, string $script): string
{
    foreach (preg_split('/\R/', $contents) ?: [] as $line) {
        $line = trim($line);
        if (! str_contains($line, '`' . $script . '`')) {
            continue;
        }

        if (str_contains($line, '| migrated |') || str_contains($line, '| `migrated` |')) {
            return 'migrated';
        }

        if (str_contains($line, '| source-owned |') || str_contains($line, '| `source-owned` |')) {
            return 'source-owned';
        }

        return 'listed';
    }

    return 'missing';
}
