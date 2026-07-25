<?php

declare(strict_types=1);

/**
 * Phase 1.81 quality gate runner.
 *
 * Hotfix over Phase 1.79: tools:hygiene now respects known durable tools that
 * are intentionally kept and covered by tests. Noisy names alone are no longer
 * enough to flag a tool when the project has declared it durable.
 *
 * Usage:
 *   php8.5 tools/gate.php [--strict]
 */

$root = dirname(__DIR__);
$toolsDir = $root . '/tools';
$siteLookupCli = $root . '/tools/site-lookup.php';
$strict = in_array('--strict', $argv, true);

$durableToolAllowlist = [
    'apply-admin-form-config-aggregator-layered-loader.php' => 'Test-protected admin form config aggregator layered loader repair tool.',
    'apply-admin-form-config-layered-loader.php' => 'Test-protected admin form config layered loader migration tool.',
    'apply-composer-internal-package-stability.php' => 'Test-protected Composer internal package stability repair tool.',
    'apply-composer-local-package-repositories.php' => 'Test-protected Composer local package repository repair tool.',
    'apply-rate-limit-admin-login-policy.php' => 'Test-protected rate-limit admin login policy apply tool.',
    'apply-rate-limit-admin-middleware-hook.php' => 'Test-protected rate-limit admin middleware hook apply tool.',
    'apply-role-admin-latte-cutover.php' => 'Test-protected guarded RoleAdminController Latte cutover executor.',
    'apply-role-admin-markup-view-cutover.php' => 'Test-protected Role Admin markup view cutover tool.',
    'apply-site-lookup-service-binding.php' => 'Recent site lookup service binding finalisation tool retained for rollback/auditability.',
    'cleanup-expired-rate-limit-buckets.php' => 'Test-protected dry-run-first expired rate-limit bucket cleanup command.',
];

/**
 * Gate check: site-lookup boundary audit (reuses the unified CLI).
 */
$siteLookupAudit = static function () use ($root, $siteLookupCli): array {
    $name = 'site-lookup:audit';

    if (!is_file($siteLookupCli)) {
        return [
            'name' => $name,
            'errors' => 1,
            'warnings' => 0,
            'summary' => 'tools/site-lookup.php not found; run the 1.77 consolidation phase first.',
            'details' => [],
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
        $errorCount = $exitCode === 0 ? 0 : 1;
    }

    return [
        'name' => $name,
        'errors' => $errorCount,
        'warnings' => 0,
        'summary' => $errorCount === 0
            ? 'No site-lookup boundary hot-path regressions detected.'
            : "{$errorCount} site-lookup boundary hard error(s) detected.",
        'details' => [],
    ];
};

/**
 * Gate check: tools/ hygiene. Flags likely tool drift while respecting known
 * durable tools that intentionally survive cleanup phases.
 */
$toolsHygiene = static function () use ($toolsDir, $durableToolAllowlist): array {
    $name = 'tools:hygiene';
    $details = [];
    $durablePresent = 0;

    if (!is_dir($toolsDir)) {
        return [
            'name' => $name,
            'errors' => 0,
            'warnings' => 0,
            'summary' => 'No tools/ directory found; nothing to check.',
            'details' => [],
        ];
    }

    $files = glob($toolsDir . '/*.php') ?: [];
    $basenames = array_map('basename', $files);

    $oneOffPattern = '/^(cleanup|apply|fix|migrate-once|one-off)[-_].+\.php$/i';
    foreach ($basenames as $base) {
        if (isset($durableToolAllowlist[$base])) {
            $durablePresent++;
            continue;
        }

        if (preg_match($oneOffPattern, $base) === 1) {
            $details[] = "Possible leftover one-off helper: tools/{$base} (delete after use if no longer needed, or add to the durable tool allowlist with a reason).";
        }
    }

    foreach ($files as $file) {
        $base = basename($file);
        if (isset($durableToolAllowlist[$base])) {
            continue;
        }

        $contents = (string) file_get_contents($file);
        $stripped = trim(preg_replace('/<\?php|declare\(strict_types=1\);/', '', $contents) ?? '');
        if ($stripped === '') {
            $details[] = 'Empty or near-empty tool file: tools/' . $base . '.';
        }
    }

    foreach ($basenames as $base) {
        if (isset($durableToolAllowlist[$base])) {
            continue;
        }

        if (preg_match('/^(.*)-v\d+\.php$/', $base, $m) === 1) {
            $baseName = $m[1] . '.php';
            if (in_array($baseName, $basenames, true) && !isset($durableToolAllowlist[$baseName])) {
                $details[] = "Versioned duplicate: tools/{$base} exists alongside tools/{$baseName} (consolidate).";
            }
        }
    }

    $warningCount = count($details);
    $durableNote = $durablePresent > 0 ? " {$durablePresent} durable tool(s) were recognised and ignored." : '';

    return [
        'name' => $name,
        'errors' => 0,
        'warnings' => $warningCount,
        'summary' => $warningCount === 0
            ? 'tools/ looks clean; no obvious drift detected.' . $durableNote
            : "{$warningCount} hygiene warning(s) in tools/." . $durableNote,
        'details' => $details,
    ];
};

/**
 * Registered gate checks.
 *
 * @var array<int, callable(): array{name:string,errors:int,warnings:int,summary:string,details:array<int,string>}> $checks
 */
$checks = [
    $siteLookupAudit,
    $toolsHygiene,
];

$results = [];
$totalErrors = 0;
$totalWarnings = 0;

foreach ($checks as $check) {
    $result = $check();
    $result += ['errors' => 0, 'warnings' => 0, 'details' => []];
    $results[] = $result;
    $totalErrors += (int) $result['errors'];
    $totalWarnings += (int) $result['warnings'];
}

$effectiveErrors = $strict ? $totalErrors + $totalWarnings : $totalErrors;

echo "## Zoosper Quality Gate\n\n";
echo 'Generated: ' . gmdate('c') . "\n";
echo 'Mode: ' . ($strict ? 'strict (warnings fail)' : 'standard (warnings advisory)') . "\n";
echo 'Checks run: ' . count($results) . "\n";
echo 'Total errors: ' . $totalErrors . "\n";
echo 'Total warnings: ' . $totalWarnings . "\n\n";

echo "### Results\n";
foreach ($results as $result) {
    $failed = $result['errors'] > 0 || ($strict && $result['warnings'] > 0);
    $status = $failed ? 'FAIL' : ($result['warnings'] > 0 ? 'WARN' : 'PASS');
    echo "- [{$status}] {$result['name']}: {$result['summary']}\n";
    foreach ($result['details'] as $detail) {
        echo "    - {$detail}\n";
    }
}
echo "\n";

if ($effectiveErrors === 0) {
    echo $totalWarnings > 0
        ? "Gate passed with advisory warnings. Run with --strict to enforce hygiene.\n"
        : "All gate checks passed.\n";
} else {
    echo "Gate failed: resolve the issues above before committing/pushing.\n";
}

exit($effectiveErrors === 0 ? 0 : 1);
