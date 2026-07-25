<?php

declare(strict_types=1);

/**
 * Repository lean hygiene audit.
 *
 * Modes:
 * - report: observations/warnings only
 * - --strict: fail only on obsolete Page Momentum/process hygiene issues
 * - --global-strict: fail on all tool-pinning tests across the repo
 */

$root = dirname(__DIR__);
$strict = in_array('--strict', $argv, true);
$globalStrict = in_array('--global-strict', $argv, true);
$reportDir = $root . '/var/reports';

$hardIssues = [];
$warnings = [];
$observations = [];

$activeProcessPatterns = [
    '/page-admin-momentum-phase-1\.[0-9]+/i',
    '/audit-page-admin-momentum-phase-[0-9]+/i',
    '/prove-page-admin-momentum-/i',
    '/generate-page-admin-momentum-/i',
    '/write-page-admin-momentum-/i',
    '/page-admin-momentum-.*(candidate|preview|preflight|cutover|patch-draft|integration-plan|hook-candidate|source-hook)/i',
];

$obsoleteToolPathPatterns = [
    '/tools\/(audit|prove|generate|write|apply|fix|repair)-page-admin-momentum-(phase-|.*(candidate|preview|preflight|cutover|bridge|hook|aggregation|source))/i',
    '/tools\/audit-page-admin-momentum-runtime-bridge\.php/i',
    '/tools\/audit-page-admin-momentum-live-aggregation\.php/i',
    '/tools\/audit-page-admin-momentum-live-duplicates\.php/i',
    '/tools\/audit-page-admin-momentum-live-smoke\.php/i',
];

$allowedActiveBasenames = [
    'cleanup-page-momentum-process-artifacts.php',
    'cleanup-page-momentum-support-artifacts.php',
    'cleanup-page-momentum-post-runtime-support-artifacts.php',
    'audit-page-momentum-cleanup-closure.php',
    'audit-page-momentum-runtime-dependencies.php',
    'plan-page-momentum-runtime-consolidation.php',
    'audit-repository-lean-hygiene.php',
    'audit-repository-file-count-baseline.php',
    'page-momentum-process-debt-cleanup-runtime-guard.md',
    'page-momentum-support-artifact-cleanup.md',
    'page-momentum-post-runtime-support-cleanup.md',
    'page-momentum-cleanup-closure.md',
    'repository-lean-hygiene.md',
    'repository-lean-hygiene-scope-fix.md',
    'repository-file-count-baseline.md',
    'roadmap-status-fragment-phase-1.63a-l.md',
    'roadmap-status-fragment-phase-1.63m-z.md',
    'roadmap-status-fragment-phase-1.64a-l.md',
    'roadmap-status-fragment-phase-1.64m-z.md',
    'roadmap-status-fragment-phase-1.65a-l.md',
    'roadmap-status-fragment-phase-1.65m-z.md',
    'roadmap-status-fragment-phase-1.66a-l.md',
    'roadmap-status-fragment-phase-1.66m-z.md',
];

$activeScanScopes = [
    $root . '/tools',
    $root . '/docs/development',
    $root . '/docs/roadmap',
    $root . '/app/zoosper-core/tests/Unit/Architecture',
];

$activeProcessFiles = [];
foreach (listFiles($activeScanScopes) as $relative => $path) {
    if (in_array(basename($relative), $allowedActiveBasenames, true)) {
        continue;
    }

    foreach ($activeProcessPatterns as $pattern) {
        if (preg_match($pattern, $relative) === 1) {
            $activeProcessFiles[] = $relative;
            break;
        }
    }
}

if ($activeProcessFiles !== []) {
    $warnings[] = 'Active Page Momentum process/support artefacts remain outside quarantine: ' . count($activeProcessFiles);
}

$quarantineLeaks = [];
foreach (listFiles([$root . '/var/quarantine', $root . '/var/reports']) as $relative => $path) {
    if (str_starts_with($relative, 'var/quarantine/') || str_starts_with($relative, 'var/reports/')) {
        $quarantineLeaks[] = $relative;
    }
}
if ($quarantineLeaks !== []) {
    $observations[] = 'Generated var/ artefacts exist locally. They should stay untracked: ' . count($quarantineLeaks);
}

$toolPinningTests = [];
$pageMomentumToolPinningTests = [];
$obsoletePageMomentumToolPinningTests = [];
foreach (listFiles([$root . '/app/zoosper-core/tests', $root . '/app/zoosper-page/tests']) as $relative => $path) {
    if (!str_ends_with($relative, '.php')) {
        continue;
    }

    $source = @file_get_contents($path);
    if ($source === false) {
        continue;
    }

    if (str_contains($source, '/tools/') || preg_match('/tools\/[a-z0-9_\-.]+\.php/i', $source) === 1) {
        $toolPinningTests[] = $relative;
        if (isPageMomentumPathOrSource($relative, $source)) {
            $pageMomentumToolPinningTests[] = $relative;
            if (containsPattern($source, $obsoleteToolPathPatterns)) {
                $obsoletePageMomentumToolPinningTests[] = $relative;
            }
        }
    }
}

if ($toolPinningTests !== []) {
    $observations[] = 'Tests still appear to pin tool file paths globally: ' . count($toolPinningTests);
}
if ($pageMomentumToolPinningTests !== []) {
    $observations[] = 'Page Momentum tests still pin current/durable or historical tool paths: ' . count($pageMomentumToolPinningTests);
}
if ($obsoletePageMomentumToolPinningTests !== []) {
    $warnings[] = 'Page Momentum tests still pin obsolete process tools: ' . count($obsoletePageMomentumToolPinningTests);
}

$pageMomentumToolCount = countMatchingFiles([$root . '/tools'], '/page-admin-momentum|page-momentum/i');
$pageMomentumDocCount = countMatchingFiles([$root . '/docs'], '/page-admin-momentum|page-momentum/i');
$observations[] = 'Active Page Momentum tool count: ' . $pageMomentumToolCount;
$observations[] = 'Active Page Momentum doc count: ' . $pageMomentumDocCount;

if ($strict || $globalStrict) {
    foreach ($activeProcessFiles as $file) {
        $hardIssues[] = 'Page Momentum process/support artefact still active: ' . $file;
    }
    foreach (array_unique($obsoletePageMomentumToolPinningTests) as $file) {
        $hardIssues[] = 'Page Momentum test pins obsolete tool path: ' . $file;
    }
}

if ($globalStrict) {
    foreach ($toolPinningTests as $file) {
        if (!in_array($file, $obsoletePageMomentumToolPinningTests, true)) {
            $hardIssues[] = 'Global test pins tool path: ' . $file;
        }
    }
}

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$mode = $globalStrict ? 'global-strict' : ($strict ? 'strict' : 'report');
$report = [];
$report[] = '## Repository Lean Hygiene Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = 'Mode: ' . $mode;
$report[] = '';
$report[] = 'Hard issues: ' . count($hardIssues);
$report[] = 'Warnings: ' . count($warnings);
$report[] = 'Observations: ' . count($observations);
$report[] = '';
$report[] = '### Observations';
foreach ($observations as $observation) {
    $report[] = '- ' . $observation;
}
$report[] = '';
$report[] = '### Warnings';
foreach ($warnings as $warning) {
    $report[] = '- ' . $warning;
}
if ($activeProcessFiles !== []) {
    $report[] = '';
    $report[] = '### Active Page Momentum process/support artefacts';
    foreach ($activeProcessFiles as $file) {
        $report[] = '- ' . $file;
    }
}
if ($obsoletePageMomentumToolPinningTests !== []) {
    $report[] = '';
    $report[] = '### Page Momentum tests pinning obsolete tool paths';
    foreach (array_unique($obsoletePageMomentumToolPinningTests) as $file) {
        $report[] = '- ' . $file;
    }
}
if ($globalStrict && $toolPinningTests !== []) {
    $report[] = '';
    $report[] = '### Global tests pinning tool paths';
    foreach ($toolPinningTests as $file) {
        $report[] = '- ' . $file;
    }
}
if ($hardIssues !== []) {
    $report[] = '';
    $report[] = '### Hard issues';
    foreach ($hardIssues as $issue) {
        $report[] = '- ' . $issue;
    }
}

file_put_contents($reportDir . '/repository-lean-hygiene.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/repository-lean-hygiene.json', json_encode([
    'generatedAt' => gmdate('c'),
    'mode' => $mode,
    'hardIssues' => $hardIssues,
    'warnings' => $warnings,
    'observations' => $observations,
    'activeProcessFiles' => $activeProcessFiles,
    'pageMomentumToolPinningTests' => $pageMomentumToolPinningTests,
    'obsoletePageMomentumToolPinningTests' => array_values(array_unique($obsoletePageMomentumToolPinningTests)),
    'toolPinningTests' => $toolPinningTests,
    'pageMomentumToolCount' => $pageMomentumToolCount,
    'pageMomentumDocCount' => $pageMomentumDocCount,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($hardIssues === [] ? 0 : 1);

function containsPattern(string $source, array $patterns): bool
{
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $source) === 1) {
            return true;
        }
    }
    return false;
}

function isPageMomentumPathOrSource(string $relative, string $source): bool
{
    $path = strtolower($relative);
    $text = strtolower($source);
    return str_contains($path, 'pageadminmomentum')
        || str_contains($path, 'page-admin-momentum')
        || str_contains($path, 'page_momentum')
        || str_contains($path, 'pagemomentum')
        || str_contains($text, 'page-admin-momentum')
        || str_contains($text, 'page_momentum')
        || str_contains($text, 'pagemomentum');
}

/**
 * @param list<string> $scopes
 * @return array<string,string>
 */
function listFiles(array $scopes): array
{
    $root = dirname(__DIR__);
    $files = [];
    foreach ($scopes as $scope) {
        if (!is_dir($scope)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scope, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile()) {
                continue;
            }
            $path = $file->getPathname();
            $files[str_replace($root . '/', '', $path)] = $path;
        }
    }
    ksort($files);
    return $files;
}

/**
 * @param list<string> $scopes
 */
function countMatchingFiles(array $scopes, string $pattern): int
{
    $count = 0;
    foreach (array_keys(listFiles($scopes)) as $relative) {
        if (preg_match($pattern, $relative) === 1) {
            $count++;
        }
    }
    return $count;
}
