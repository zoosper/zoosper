<?php

declare(strict_types=1);

/**
 * Phase 1.78 quality gate runner.
 *
 * A single, extensible entry point that runs registered guard checks and exits
 * non-zero if any of them report hard errors. Designed to drop straight into
 * CI or a pre-push hook.
 *
 * Adding a new gate check: append an entry to the $checks array below. Each
 * check is a callable returning ['name'=>string,'errors'=>int,'summary'=>string].
 * No new files per gate keeps the tools directory lean.
 *
 * Usage:
 *   php8.5 tools/gate.php
 */

$root = dirname(__DIR__);
$siteLookupCli = $root . '/tools/site-lookup.php';

/**
 * Run the site-lookup boundary audit as a gate check.
 *
 * Reuses the unified CLI as a subprocess so the audit logic lives in exactly
 * one place. Falls back to parsing the JSON report the CLI writes.
 */
$siteLookupAudit = static function () use ($root, $siteLookupCli): array {
    $name = 'site-lookup:audit';

    if (!is_file($siteLookupCli)) {
        return [
            'name' => $name,
            'errors' => 1,
            'summary' => 'tools/site-lookup.php not found; run the 1.77 consolidation phase first.',
        ];
    }

    $phpBinary = PHP_BINARY ?: 'php';
    $command = escapeshellarg($phpBinary) . ' ' . escapeshellarg($siteLookupCli) . ' audit';

    $output = [];
    $exitCode = 0;
    exec($command . ' 2>&1', $output, $exitCode);

    $reportPath = $root . '/docs/reports/site-lookup-boundary-drift.json';
    $errorCount = null;

    if (is_file($reportPath)) {
        $decoded = json_decode((string) file_get_contents($reportPath), true);
        if (is_array($decoded) && isset($decoded['errors']) && is_array($decoded['errors'])) {
            $errorCount = count($decoded['errors']);
        }
    }

    if ($errorCount === null) {
        // Fall back to the CLI exit code if the report could not be parsed.
        $errorCount = $exitCode === 0 ? 0 : 1;
    }

    return [
        'name' => $name,
        'errors' => $errorCount,
        'summary' => $errorCount === 0
            ? 'No site-lookup boundary hot-path regressions detected.'
            : "{$errorCount} site-lookup boundary hard error(s) detected.",
    ];
};

/**
 * Registered gate checks. Append new callables here to extend the gate.
 *
 * @var array<int, callable(): array{name:string,errors:int,summary:string}> $checks
 */
$checks = [
    $siteLookupAudit,
];

$results = [];
$totalErrors = 0;

foreach ($checks as $check) {
    $result = $check();
    $results[] = $result;
    $totalErrors += (int) $result['errors'];
}

echo "## Zoosper Quality Gate\n\n";
echo 'Generated: ' . gmdate('c') . "\n";
echo 'Checks run: ' . count($results) . "\n";
echo 'Total errors: ' . $totalErrors . "\n\n";

echo "### Results\n";
foreach ($results as $result) {
    $status = $result['errors'] === 0 ? 'PASS' : 'FAIL';
    echo "- [{$status}] {$result['name']}: {$result['summary']}\n";
}
echo "\n";

if ($totalErrors === 0) {
    echo "All gate checks passed.\n";
} else {
    echo "Gate failed: resolve the errors above before committing/pushing.\n";
}

exit($totalErrors === 0 ? 0 : 1);
