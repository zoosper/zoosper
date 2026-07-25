<?php

declare(strict_types=1);

/**
 * Dependency-aware Page Momentum runtime consolidation planner.
 *
 * Dry-run by default. With --apply, only candidates that are not required by
 * active runtime code or blocked candidates are moved into var/quarantine.
 */

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);
$timestamp = gmdate('Ymd-His');
$reportDir = $root . '/var/reports';
$quarantineRoot = $root . '/var/quarantine/page-momentum-runtime-candidates/' . $timestamp;

$expectedRuntimeCore = [
    'app/zoosper-page/config/admin_page_momentum.php',
    'app/zoosper-page/config/admin_page_momentum_menu.php',
    'app/zoosper-page/config/admin_page_momentum_routes.php',
    'app/zoosper-page/resources/views/admin/page-momentum.latte',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminController.php',
    'app/zoosper-page/src/Admin/Controller/PageMomentumAdminHttpController.php',
    'app/zoosper-page/src/Admin/PageMomentum/PageMomentumDashboardFact.php',
    'app/zoosper-page/src/Admin/PageMomentum/PageMomentumDashboardFactsProvider.php',
    'app/zoosper-page/src/Admin/PageMomentumAdminDashboardShell.php',
    'app/zoosper-page/src/Admin/PageMomentumAdminResponseFactory.php',
    'app/zoosper-page/src/Admin/PageMomentumStatusProvider.php',
];

$candidatePatterns = [
    '/admin_page_momentum_.*(candidate|hook|aggregation|runtime|source|adapter)/i',
    '/PageMomentum.*(ActivationGuard|AggregationBridge|HookProvider|LiveAggregationIntegrator|MenuBridge|RouteBridge|RouteMenuHook|RuntimeAggregationProvider|SourceHookAdapter|AggregatorPatchBuilder|RuntimeBridge|DuplicateGuard|DefinitionProvider|RouteDefinitionProvider|MenuDefinitionProvider)\.php$/',
];

$allFiles = listProjectFiles($root);
$candidates = [];
$protected = [];

foreach (array_keys($allFiles) as $relative) {
    if (!isPageMomentumFile($relative)) {
        continue;
    }

    if (in_array($relative, $expectedRuntimeCore, true)) {
        $protected[] = $relative;
        continue;
    }

    if (!str_starts_with($relative, 'app/')) {
        continue;
    }

    foreach ($candidatePatterns as $pattern) {
        if (preg_match($pattern, $relative) === 1) {
            $candidates[$relative] = [
                'file' => $relative,
                'symbol' => basenameWithoutExtension($relative),
                'externalRuntimeReferences' => [],
                'candidateReferences' => [],
                'blockedBecause' => [],
            ];
            break;
        }
    }
}

ksort($candidates);
sort($protected);
$candidatePaths = array_keys($candidates);
$candidateLookup = array_fill_keys($candidatePaths, true);

foreach ($candidates as $relative => $candidate) {
    $refs = findRuntimeReferences($root, $relative, $candidate['symbol']);
    foreach ($refs as $ref) {
        if (isset($candidateLookup[$ref])) {
            $candidates[$relative]['candidateReferences'][] = $ref;
        } else {
            $candidates[$relative]['externalRuntimeReferences'][] = $ref;
        }
    }
}

$blocked = [];
foreach ($candidates as $relative => $candidate) {
    if ($candidate['externalRuntimeReferences'] !== []) {
        $blocked[$relative] = true;
        $candidates[$relative]['blockedBecause'][] = 'referenced by active non-candidate runtime files';
    }
}

// Propagate block status to dependencies of blocked candidates. If an active
// candidate must remain, anything it references must also remain.
$changed = true;
while ($changed) {
    $changed = false;
    foreach ($candidates as $relative => $candidate) {
        if (!isset($blocked[$relative])) {
            continue;
        }

        foreach ($candidate['candidateReferences'] as $dependency) {
            if (!isset($blocked[$dependency])) {
                $blocked[$dependency] = true;
                $candidates[$dependency]['blockedBecause'][] = 'referenced by blocked candidate ' . $relative;
                $changed = true;
            }
        }
    }
}

$safe = [];
$blockedDetails = [];
foreach ($candidates as $relative => $candidate) {
    if (isset($blocked[$relative])) {
        $blockedDetails[$relative] = [
            'reason' => array_values(array_unique($candidate['blockedBecause'])),
            'externalRuntimeReferences' => array_values(array_unique($candidate['externalRuntimeReferences'])),
            'candidateReferences' => array_values(array_unique($candidate['candidateReferences'])),
        ];
    } else {
        $safe[] = $relative;
    }
}

$moved = [];
$errors = [];
if ($apply) {
    if (!is_dir($quarantineRoot)) {
        mkdir($quarantineRoot, 0775, true);
    }

    foreach ($safe as $relative) {
        $source = $root . '/' . $relative;
        $target = $quarantineRoot . '/' . $relative;
        $targetDir = dirname($target);

        if (!is_file($source)) {
            continue;
        }
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        if (@rename($source, $target)) {
            $moved[] = $relative;
        } else {
            $errors[] = 'Failed to move: ' . $relative;
        }
    }

    writeRestoreScript($quarantineRoot, $root, $moved);
}

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'mode' => $apply ? 'apply' : 'dry-run',
    'summary' => [
        'expectedRuntimeCore' => count($protected),
        'runtimeCandidates' => count($candidates),
        'safeToQuarantine' => count($safe),
        'blockedByRuntimeReferences' => count($blockedDetails),
        'moved' => count($moved),
        'errors' => count($errors),
    ],
    'expectedRuntimeCore' => $protected,
    'safeToQuarantine' => $safe,
    'blockedByRuntimeReferences' => $blockedDetails,
    'moved' => $moved,
    'errors' => $errors,
    'quarantine' => $apply ? str_replace($root . '/', '', $quarantineRoot) : null,
];

$report = [];
$report[] = '## Page Momentum Runtime Consolidation Planner';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = 'Mode: ' . $payload['mode'];
$report[] = '';
$report[] = '### Summary';
foreach ($payload['summary'] as $key => $value) {
    $report[] = '- ' . $key . ': ' . $value;
}

$report[] = '';
$report[] = '### Expected runtime core protected';
foreach ($protected as $file) {
    $report[] = '- ' . $file;
}

$report[] = '';
$report[] = '### Safe to quarantine';
foreach ($safe as $file) {
    $report[] = '- ' . $file;
}

$report[] = '';
$report[] = '### Blocked by runtime references';
foreach ($blockedDetails as $file => $details) {
    $report[] = '- ' . $file;
    foreach ($details['reason'] as $reason) {
        $report[] = '  - reason: ' . $reason;
    }
    foreach ($details['externalRuntimeReferences'] as $ref) {
        $report[] = '  - external: ' . $ref;
    }
    foreach ($details['candidateReferences'] as $ref) {
        $report[] = '  - candidate dependency: ' . $ref;
    }
}

if ($apply) {
    $report[] = '';
    $report[] = 'Quarantine: ' . $payload['quarantine'];
    $report[] = 'Restore script: ' . $payload['quarantine'] . '/restore.sh';
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportDir . '/page-momentum-runtime-consolidation-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-momentum-runtime-consolidation-plan.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);

function isPageMomentumFile(string $relative): bool
{
    return preg_match('/page-admin-momentum|page-momentum|pagemomentum|page_momentum/i', $relative) === 1;
}

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
function findRuntimeReferences(string $root, string $candidateRelative, string $symbol): array
{
    $refs = [];
    if ($symbol === '') {
        return [];
    }

    foreach (listProjectFiles($root) as $relative => $path) {
        if ($relative === $candidateRelative) {
            continue;
        }
        if (!str_starts_with($relative, 'app/')) {
            continue;
        }
        if (str_contains($relative, '/tests/')) {
            continue;
        }
        if (!preg_match('/\.(php|json|xml|neon|yml|yaml|latte)$/i', $relative)) {
            continue;
        }

        $contents = @file_get_contents($path);
        if ($contents !== false && str_contains($contents, $symbol)) {
            $refs[] = $relative;
        }
    }

    sort($refs);
    return array_values(array_unique($refs));
}

/**
 * @param list<string> $moved
 */
function writeRestoreScript(string $quarantineRoot, string $root, array $moved): void
{
    $lines = ['#!/usr/bin/env bash', 'set -euo pipefail'];
    foreach ($moved as $relative) {
        $source = $quarantineRoot . '/' . $relative;
        $target = $root . '/' . $relative;
        $lines[] = 'mkdir -p ' . escapeshellarg(dirname($target));
        $lines[] = 'mv ' . escapeshellarg($source) . ' ' . escapeshellarg($target);
    }

    file_put_contents($quarantineRoot . '/restore.sh', implode("\n", $lines) . "\n");
    chmod($quarantineRoot . '/restore.sh', 0755);
}
