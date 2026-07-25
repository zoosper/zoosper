<?php

declare(strict_types=1);

/**
 * Page Momentum runtime dependency audit.
 *
 * Read-only classifier for remaining active Page Momentum files after the cleanup
 * arc. This helps identify what is truly runtime-critical before any future
 * consolidation/removal step.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';

$expectedRuntimeCore = [
    'app/zoosper-page/config/admin_page_momentum.php',
    'app/zoosper-page/config/admin_page_momentum_menu.php',
    'app/zoosper-page/config/admin_page_momentum_routes.php',
    'app/zoosper-page/resources/views/admin/page-momentum.latte',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminHttpController.php',
    'app/zoosper-page/src/Admin/PageMomentumAdminDashboardShell.php',
    'app/zoosper-page/src/Admin/PageMomentumAdminResponseFactory.php',
    'app/zoosper-page/src/Admin/PageMomentumStatusProvider.php',
    'app/zoosper-page/src/Admin/PageAdminLaunchReadinessProvider.php',
    'app/zoosper-page/src/Admin/PageAdminDashboardIndicatorProvider.php',
    'app/zoosper-page/src/Admin/PageAdminDashboardStatusPresenter.php',
    'app/zoosper-page/src/Admin/PageAdminDashboardFactProvider.php',
    'app/zoosper-page/src/Admin/PageMomentum/PageMomentumDashboardFact.php',
    'app/zoosper-page/src/Admin/PageMomentum/PageMomentumDashboardFactsProvider.php',
];

$knownBridgeOrCandidateHints = [
    'candidate',
    'bridge',
    'hook',
    'aggregator',
    'aggregation',
    'activationguard',
    'duplicateguard',
    'integrator',
];

$files = listProjectFiles($root);
$pageMomentumFiles = array_values(array_filter(
    array_keys($files),
    static fn (string $path): bool => preg_match('/page-admin-momentum|page-momentum|pagemomentum|page_momentum/i', $path) === 1,
));

$existingExpected = [];
$missingExpected = [];
foreach ($expectedRuntimeCore as $path) {
    if (is_file($root . '/' . $path)) {
        $existingExpected[] = $path;
    } else {
        $missingExpected[] = $path;
    }
}

$keepRuntime = [];
$reviewCandidate = [];
$configCandidate = [];
$supportOnly = [];

foreach ($pageMomentumFiles as $relative) {
    if (in_array($relative, $expectedRuntimeCore, true)) {
        $keepRuntime[] = $relative;
        continue;
    }

    if (str_starts_with($relative, 'docs/') || str_starts_with($relative, 'tools/')) {
        $supportOnly[] = $relative;
        continue;
    }

    if (str_contains($relative, '/config/')) {
        $configCandidate[] = [
            'file' => $relative,
            'reason' => 'Page Momentum config outside the current expected runtime core; verify whether it is still loaded.',
            'references' => findReferences($root, $relative),
        ];
        continue;
    }

    $lower = strtolower($relative);
    $hinted = false;
    foreach ($knownBridgeOrCandidateHints as $hint) {
        if (str_contains($lower, $hint)) {
            $hinted = true;
            break;
        }
    }

    $reviewCandidate[] = [
        'file' => $relative,
        'reason' => $hinted
            ? 'Name suggests bridge/hook/candidate/aggregation scaffolding; verify if it can be folded into durable providers.'
            : 'Page Momentum file outside the expected runtime core; review manually.',
        'references' => findReferences($root, basenameWithoutExtension($relative)),
    ];
}

sort($keepRuntime);
sort($supportOnly);

$payload = [
    'generatedAt' => gmdate('c'),
    'summary' => [
        'pageMomentumFiles' => count($pageMomentumFiles),
        'keepRuntime' => count($keepRuntime),
        'reviewCandidate' => count($reviewCandidate),
        'configCandidate' => count($configCandidate),
        'supportOnly' => count($supportOnly),
        'missingExpectedRuntimeCore' => count($missingExpected),
    ],
    'keepRuntime' => $keepRuntime,
    'reviewCandidate' => $reviewCandidate,
    'configCandidate' => $configCandidate,
    'supportOnly' => $supportOnly,
    'missingExpectedRuntimeCore' => $missingExpected,
];

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$report = [];
$report[] = '## Page Momentum Runtime Dependency Audit';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = '';
$report[] = '### Summary';
foreach ($payload['summary'] as $key => $value) {
    $report[] = '- ' . $key . ': ' . $value;
}

$report[] = '';
$report[] = '### Keep runtime';
foreach ($keepRuntime as $file) {
    $report[] = '- ' . $file;
}

$report[] = '';
$report[] = '### Config candidates';
foreach ($configCandidate as $candidate) {
    $report[] = '- ' . $candidate['file'];
    $report[] = '  - reason: ' . $candidate['reason'];
    $report[] = '  - references: ' . count($candidate['references']);
}

$report[] = '';
$report[] = '### Review candidates';
foreach ($reviewCandidate as $candidate) {
    $report[] = '- ' . $candidate['file'];
    $report[] = '  - reason: ' . $candidate['reason'];
    $report[] = '  - references: ' . count($candidate['references']);
}

$report[] = '';
$report[] = '### Support only';
foreach ($supportOnly as $file) {
    $report[] = '- ' . $file;
}

if ($missingExpected !== []) {
    $report[] = '';
    $report[] = '### Missing expected runtime core';
    foreach ($missingExpected as $file) {
        $report[] = '- ' . $file;
    }
}

file_put_contents($reportDir . '/page-momentum-runtime-dependencies.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-momentum-runtime-dependencies.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($missingExpected === [] ? 0 : 1);

function basenameWithoutExtension(string $relative): string
{
    $basename = basename($relative);
    return str_ends_with($basename, '.php') ? substr($basename, 0, -4) : $basename;
}

/**
 * @return array<string,string>
 */
function listProjectFiles(string $root): array
{
    $excludedPrefixes = ['.git/', 'vendor/', 'var/', 'node_modules/'];
    $files = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $path = $file->getPathname();
        $relative = str_replace($root . '/', '', $path);
        foreach ($excludedPrefixes as $prefix) {
            if (str_starts_with($relative, $prefix)) {
                continue 2;
            }
        }
        $files[$relative] = $path;
    }

    ksort($files);
    return $files;
}

/**
 * @return list<string>
 */
function findReferences(string $root, string $needle): array
{
    if ($needle === '') {
        return [];
    }

    $refs = [];
    foreach (listProjectFiles($root) as $relative => $path) {
        if (!preg_match('/\.(php|md|txt|json|xml|yml|yaml|latte)$/i', $relative)) {
            continue;
        }

        $contents = @file_get_contents($path);
        if ($contents !== false && str_contains($contents, $needle)) {
            $refs[] = $relative;
        }
    }

    sort($refs);
    return array_values(array_unique($refs));
}
