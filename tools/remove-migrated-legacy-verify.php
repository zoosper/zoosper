<?php

declare(strict_types=1);

/**
 * Controlled removal helper for migrated legacy tools/verify-*.php scripts.
 *
 * Dry-run is the default. Actual deletion is deliberately hard-gated and
 * requires --apply, --confirm-pest-coverage, --confirm-remove, and a `migrated`
 * status in docs/development/legacy-verify-migration-status.md.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
$statusPath = $root . DIRECTORY_SEPARATOR . 'docs' . DIRECTORY_SEPARATOR . 'development' . DIRECTORY_SEPARATOR . 'legacy-verify-migration-status.md';
$script = null;
$apply = false;
$confirmPestCoverage = false;
$confirmRemove = false;

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--script=')) {
        $script = substr($argument, strlen('--script='));
    } elseif ($argument === '--apply') {
        $apply = true;
    } elseif ($argument === '--confirm-pest-coverage') {
        $confirmPestCoverage = true;
    } elseif ($argument === '--confirm-remove') {
        $confirmRemove = true;
    } elseif (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if ($script === null || $script === '') {
    fwrite(STDERR, 'Usage: php tools/remove-migrated-legacy-verify.php --script=tools/verify-example.php [--apply --confirm-pest-coverage --confirm-remove] [--output-dir=/tmp/out]' . PHP_EOL);
    exit(1);
}

$normalisedScript = normaliseScriptPath($script);
validateLegacyVerifyPath($normalisedScript);

if (! in_array($normalisedScript, allowedPilotScripts(), true)) {
    fwrite(STDERR, 'Refusing to remove non-allowlisted legacy verify script: ' . $normalisedScript . PHP_EOL);
    exit(1);
}

$status = migrationStatusFor($statusPath, $normalisedScript);

if ($apply && $status !== 'migrated') {
    fwrite(STDERR, 'Refusing apply because migration ledger status is not migrated for ' . $normalisedScript . ' (status: ' . $status . ')' . PHP_EOL);
    exit(1);
}

if ($apply && (! $confirmPestCoverage || ! $confirmRemove)) {
    fwrite(STDERR, 'Refusing apply without --confirm-pest-coverage and --confirm-remove: ' . $normalisedScript . PHP_EOL);
    exit(1);
}

$scriptPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalisedScript);

if (! is_file($scriptPath)) {
    fwrite(STDERR, 'Legacy verify script not found: ' . $normalisedScript . PHP_EOL);
    exit(1);
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'legacy-verify-controlled-removal-' . basename($normalisedScript, '.php') . '.txt';

$lines = [];
$lines[] = '# Legacy Verify Controlled Removal';
$lines[] = '';
$lines[] = 'Script: ' . $normalisedScript;
$lines[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
$lines[] = 'Migration status: ' . $status;
$lines[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$lines[] = '';
$lines[] = 'Safety gate: equivalent Pest coverage and migrated ledger status must exist before deletion.';
$lines[] = 'Allowed pilot script: yes';
$lines[] = 'Apply confirmations: ' . ($confirmPestCoverage && $confirmRemove ? 'complete' : 'incomplete');

if ($apply) {
    if (! unlink($scriptPath)) {
        fwrite(STDERR, 'Failed to remove legacy verify script: ' . $normalisedScript . PHP_EOL);
        exit(1);
    }

    $lines[] = 'Result: removed';
    echo 'Removed migrated legacy verify script: ' . $normalisedScript . PHP_EOL;
} else {
    $lines[] = 'Result: dry-run only; no files changed';
    echo 'Dry-run only; no files changed for: ' . $normalisedScript . PHP_EOL;
}

file_put_contents($reportPath, implode(PHP_EOL, $lines) . PHP_EOL);
echo 'Removal report written to: ' . $reportPath . PHP_EOL;

function normaliseScriptPath(string $script): string
{
    $normalised = str_replace('\\', '/', trim($script));
    $normalised = preg_replace('#/+#', '/', $normalised) ?? $normalised;

    if (str_contains($normalised, '..')) {
        fwrite(STDERR, 'Refusing unsafe path traversal candidate: ' . $script . PHP_EOL);
        exit(1);
    }

    return ltrim($normalised, '/');
}

function validateLegacyVerifyPath(string $script): void
{
    if (! str_starts_with($script, 'tools/verify-') || ! str_ends_with($script, '.php')) {
        fwrite(STDERR, 'Refusing non legacy verify script: ' . $script . PHP_EOL);
        exit(1);
    }
}

function migrationStatusFor(string $statusPath, string $script): string
{
    if (! is_file($statusPath)) {
        fwrite(STDERR, 'Migration status ledger not found: ' . $statusPath . PHP_EOL);
        exit(1);
    }

    $contents = (string) file_get_contents($statusPath);

    foreach (preg_split('/\R/', $contents) ?: [] as $line) {
        $line = trim($line);

        if (! str_starts_with($line, '| `' . $script . '` |')) {
            continue;
        }

        $columns = array_map('trim', explode('|', trim($line, '|')));
        if (count($columns) < 2) {
            break;
        }

        return trim($columns[1], '` ');
    }

    fwrite(STDERR, 'Migration status ledger does not include script: ' . $script . PHP_EOL);
    exit(1);
}

/**
 * @return list<string>
 */
function allowedPilotScripts(): array
{
    return [
        'tools/verify-project-structure.php',
        'tools/verify-runtime-path-safety.php',
        'tools/verify-service-provider-manifest-file.php',
        'tools/verify-module-composer-manifests.php',
        'tools/verify-roadmap-planning-docs.php',
    ];
}
