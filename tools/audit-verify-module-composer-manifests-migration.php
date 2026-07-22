<?php

declare(strict_types=1);

/**
 * Audit replacement Pest coverage for tools/verify-module-composer-manifests.php.
 *
 * This command is read-only. It does not delete or rewrite source files.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

$legacyScript = 'tools/verify-module-composer-manifests.php';
$coverageTest = 'app/zoosper-core/tests/Unit/Tools/LegacyVerifyModuleComposerManifestsCoverageTest.php';
$statusDoc = 'docs/development/legacy-verify-migration-status.md';
$migrationDoc = 'docs/development/verify-module-composer-manifests-migration.md';
$rootComposer = 'composer.json';
$mediaComposer = 'packages/zoosper-media/composer.json';
$removalTool = 'tools/remove-migrated-legacy-verify.php';

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$legacyPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $legacyScript);
$legacyExists = is_file($legacyPath);
$status = 'unknown';
$statusPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $statusDoc);

if (is_file($statusPath)) {
    $status = migrationStatusFor((string) file_get_contents($statusPath), $legacyScript);
}

$checks = [
    'legacy_script_exists' => $legacyExists,
    'coverage_test_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $coverageTest)),
    'migration_doc_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $migrationDoc)),
    'status_doc_exists' => is_file($statusPath),
    'root_composer_exists' => is_file($root . DIRECTORY_SEPARATOR . $rootComposer),
    'media_composer_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $mediaComposer)),
    'removal_tool_exists' => is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $removalTool)),
];

$errors = [];
foreach ($checks as $name => $passed) {
    if (! $passed) {
        $errors[] = $name . ' failed';
    }
}

if ($status !== 'source-owned') {
    $errors[] = 'Expected migration status source-owned before deletion, found: ' . $status;
}

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'verify-module-composer-manifests-migration.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'verify-module-composer-manifests-migration.log';

$report = [];
$report[] = '# Verify Module Composer Manifests Migration Evidence';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Legacy script: ' . $legacyScript;
$report[] = 'Legacy script exists: ' . ($legacyExists ? 'yes' : 'no');
$report[] = 'Replacement Pest coverage: ' . $coverageTest;
$report[] = 'Migration status: ' . $status;
$report[] = 'Errors: ' . count($errors);
$report[] = '';

foreach ($checks as $name => $passed) {
    $report[] = '- ' . $name . ': ' . ($passed ? 'pass' : 'fail');
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Verify module composer manifests migration evidence written to: ' . $txtPath;
$log[] = 'MIGRATION_STATUS ' . $status;
$log[] = 'LEGACY_SCRIPT_EXISTS ' . ($legacyExists ? 'yes' : 'no');
$log[] = 'EVIDENCE_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

if ($errors !== []) {
    exit(1);
}

function migrationStatusFor(string $contents, string $script): string
{
    foreach (preg_split('/\R/', $contents) ?: [] as $line) {
        $line = trim($line);
        if (! str_starts_with($line, '| `' . $script . '` |')) {
            continue;
        }

        $columns = array_map('trim', explode('|', trim($line, '|')));
        return isset($columns[1]) ? trim($columns[1], '` ') : 'unknown';
    }

    return 'missing';
}
