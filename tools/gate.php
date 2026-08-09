<?php

declare(strict_types=1);

/**
 * Phase 1.84 quality gate runner.
 *
 * Unifies durable-tool knowledge behind a single manifest (config/durable-tools.php)
 * shared with the DurableToolRegistry, so the gate and the test suite can never
 * disagree about which tools are durable.
 *
 * Registered checks:
 *   site-lookup:audit           - page hot-path boundary regressions (hard errors)
 *   tools:hygiene               - leftover one-off helpers / dead tool files (warnings)
 *   durable-registry:integrity  - manifest vs disk drift (hard errors)
 *
 * Usage:
 *   php8.5 tools/gate.php [--strict]
 *
 * --strict promotes hygiene warnings into build-failing errors.
 */

$root = dirname(__DIR__);
$toolsDir = $root . '/tools';
$siteLookupCli = $root . '/tools/site-lookup.php';
$manifestPath = $root . '/config/durable-tools.php';
$strict = in_array('--strict', $argv, true);

/**
 * Load the canonical durable-tool manifest.
 *
 * @return array{map: array<string, array{reason: string}>, source: string}
 */
$loadDurableManifest = static function () use ($manifestPath): array {
    if (is_file($manifestPath)) {
        $loaded = require $manifestPath;
        if (is_array($loaded)) {
            return ['map' => $loaded, 'source' => 'config/durable-tools.php'];
        }
    }

    // Fallback keeps the gate usable if the manifest is missing, but the
    // integrity check will flag the situation loudly.
    $fallback = [
        'tools/cleanup-expired-rate-limit-buckets.php' => ['reason' => 'fallback'],
        'tools/install-git-hooks.php' => ['reason' => 'fallback'],
    ];

    return ['map' => $fallback, 'source' => 'built-in fallback (manifest missing)'];
};

$manifest = $loadDurableManifest();
$durableMap = $manifest['map'];
$manifestSource = $manifest['source'];

// Basenames for hygiene lookups (manifest keys are repo-relative paths).
$durableBasenames = [];
foreach (array_keys($durableMap) as $toolPath) {
    $durableBasenames[basename($toolPath)] = true;
}

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
            'summary' => 'tools/site-lookup.php is missing; restore the durable site-lookup audit tool.',
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
 * Gate check: tools/ hygiene. Respects the durable manifest.
 */
$toolsHygiene = static function () use ($toolsDir, $durableBasenames): array {
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
        if (isset($durableBasenames[$base])) {
            $durablePresent++;
            continue;
        }
        if (preg_match($oneOffPattern, $base) === 1) {
            $details[] = "Possible leftover one-off helper: tools/{$base} (delete after use, or add to config/durable-tools.php with a reason).";
        }
    }

    foreach ($files as $file) {
        $base = basename($file);
        if (isset($durableBasenames[$base])) {
            continue;
        }
        $contents = (string) file_get_contents($file);
        $stripped = trim(preg_replace('/<\?php|declare\(strict_types=1\);/', '', $contents) ?? '');
        if ($stripped === '') {
            $details[] = 'Empty or near-empty tool file: tools/' . $base . '.';
        }
    }

    foreach ($basenames as $base) {
        if (isset($durableBasenames[$base])) {
            continue;
        }
        if (preg_match('/^(.*)-v\d+\.php$/', $base, $m) === 1) {
            $baseName = $m[1] . '.php';
            if (in_array($baseName, $basenames, true) && !isset($durableBasenames[$baseName])) {
                $details[] = "Versioned duplicate: tools/{$base} exists alongside tools/{$baseName} (consolidate).";
            }
        }
    }

    $warningCount = count($details);
    $durableNote = $durablePresent > 0 ? " {$durablePresent} durable tool(s) recognised via manifest and ignored." : '';

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
 * Gate check: durable registry integrity. Fails on manifest/disk drift.
 */
$durableRegistryIntegrity = static function () use ($root, $durableMap, $manifestSource): array {
    $name = 'durable-registry:integrity';
    $details = [];
    $errors = 0;

    if ($manifestSource !== 'config/durable-tools.php') {
        $errors++;
        $details[] = 'Canonical manifest config/durable-tools.php is missing; using ' . $manifestSource . '.';
    }

    foreach ($durableMap as $toolPath => $meta) {
        if (!is_file($root . '/' . $toolPath)) {
            $errors++;
            $details[] = "Manifest lists {$toolPath} but it is missing on disk (recover it or remove the entry).";
        }
        $reason = is_array($meta) ? ($meta['reason'] ?? '') : '';
        if (trim((string) $reason) === '') {
            $errors++;
            $details[] = "Manifest entry {$toolPath} has an empty reason (every durable tool needs a documented reason).";
        }
    }

    return [
        'name' => $name,
        'errors' => $errors,
        'warnings' => 0,
        'summary' => $errors === 0
            ? 'Durable manifest is consistent with disk (source: ' . $manifestSource . ').'
            : "{$errors} durable manifest integrity error(s).",
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
    $durableRegistryIntegrity,
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
echo 'Manifest source: ' . $manifestSource . "\n";
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
