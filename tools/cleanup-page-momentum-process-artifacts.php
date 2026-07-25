<?php

declare(strict_types=1);

/**
 * Runtime-guarded Page Momentum process-debt cleanup helper.
 *
 * Dry-run by default. --apply quarantines only process artefacts that are not
 * referenced by production/runtime app code. Old docs/tools/tests are not
 * blockers because they are part of the same cleanup target.
 */

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);
$timestamp = gmdate('Ymd-His');
$quarantineRoot = $root . '/var/quarantine/page-momentum-process-artifacts/' . $timestamp;
$reportDir = $root . '/var/reports';

$keepBasenames = [
    'PageMomentumAdminController.php',
    'PageMomentumAdminHttpController.php',
    'PageMomentumAdminDashboardShell.php',
    'PageMomentumAdminResponseFactory.php',
    'PageMomentumStatusProvider.php',
    'PageAdminLaunchReadinessProvider.php',
    'PageAdminDashboardIndicatorProvider.php',
    'PageAdminDashboardStatusPresenter.php',
    'PageAdminDashboardFactProvider.php',
    'PageAdminDashboardFactsGuard.php',
    'PageAdminDashboardStatusSystemGuard.php',
    'PageMomentumDashboardFact.php',
    'PageMomentumDashboardFactsProvider.php',
];

$candidateScopes = [
    $root . '/app/zoosper-page/src/Admin',
    $root . '/app/zoosper-core/tests/Unit/Architecture',
    $root . '/tools',
    $root . '/docs/development',
    $root . '/docs/roadmap',
];

$runtimeScopes = [
    $root . '/app',
];

$matches = [];
foreach ($candidateScopes as $scope) {
    if (!is_dir($scope)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scope, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $relative = str_replace($root . '/', '', $file->getPathname());
        $basename = $file->getBasename();

        if (in_array($basename, $keepBasenames, true)) {
            continue;
        }

        if (isProcessArtefact($relative, $basename)) {
            $matches[$relative] = [
                'relative' => $relative,
                'basename' => $basename,
                'symbol' => symbolFromBasename($basename),
            ];
        }
    }
}
ksort($matches);

$matchedPaths = array_keys($matches);
$safe = [];
$blocked = [];

foreach ($matches as $relative => $meta) {
    $symbol = $meta['symbol'];
    $runtimeRefs = $symbol === null ? [] : findRuntimeReferences($root, $runtimeScopes, $matchedPaths, $symbol);

    if ($runtimeRefs === []) {
        $safe[] = $relative;
    } else {
        $blocked[$relative] = $runtimeRefs;
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

$report = [];
$report[] = '## Runtime-Guarded Page Momentum Process Artefact Cleanup';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
$report[] = '';
$report[] = 'Matched artefacts: ' . count($matches);
$report[] = 'Safe to quarantine: ' . count($safe);
$report[] = 'Blocked by runtime references: ' . count($blocked);
$report[] = 'Moved artefacts: ' . count($moved);
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '### Safe to quarantine';
foreach ($safe as $relative) {
    $report[] = '- ' . $relative;
}
$report[] = '';
$report[] = '### Blocked by runtime references';
foreach ($blocked as $relative => $refs) {
    $report[] = '- ' . $relative;
    foreach ($refs as $ref) {
        $report[] = '  - ' . $ref;
    }
}

if ($apply) {
    $report[] = '';
    $report[] = 'Quarantine: ' . str_replace($root . '/', '', $quarantineRoot);
    $report[] = 'Restore script: ' . str_replace($root . '/', '', $quarantineRoot . '/restore.sh');
}

file_put_contents($reportDir . '/page-momentum-process-artifact-cleanup.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-momentum-process-artifact-cleanup.json', json_encode([
    'generatedAt' => gmdate('c'),
    'mode' => $apply ? 'apply' : 'dry-run',
    'matched' => array_values($matchedPaths),
    'safeToQuarantine' => $safe,
    'blockedByRuntimeReferences' => $blocked,
    'moved' => $moved,
    'errors' => $errors,
    'quarantine' => $apply ? str_replace($root . '/', '', $quarantineRoot) : null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);

function symbolFromBasename(string $basename): ?string
{
    if (!str_ends_with($basename, '.php')) {
        return null;
    }

    return substr($basename, 0, -4);
}

/**
 * @param list<string> $matchedPaths
 * @param list<string> $runtimeScopes
 * @return list<string>
 */
function findRuntimeReferences(string $root, array $runtimeScopes, array $matchedPaths, string $symbol): array
{
    $refs = [];
    $matchedLookup = array_fill_keys($matchedPaths, true);

    foreach ($runtimeScopes as $scope) {
        if (!is_dir($scope)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scope, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof SplFileInfo || !$file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            $relative = str_replace($root . '/', '', $path);

            if (isset($matchedLookup[$relative])) {
                continue;
            }
            if (str_contains($relative, '/tests/') || str_starts_with($relative, 'docs/') || str_starts_with($relative, 'tools/')) {
                continue;
            }
            if (!preg_match('/\.(php|neon|json|xml|yml|yaml)$/i', $relative)) {
                continue;
            }

            $contents = @file_get_contents($path);
            if ($contents === false || !str_contains($contents, $symbol)) {
                continue;
            }

            $refs[] = $relative;
        }
    }

    sort($refs);
    return array_values(array_unique($refs));
}

function isProcessArtefact(string $relative, string $basename): bool
{
    $path = strtolower($relative);
    $name = strtolower($basename);

    if (str_contains($path, 'cleanup-page-momentum-process-artifacts.php')) {
        return false;
    }

    $pageMomentum = str_contains($path, 'page-momentum')
        || str_contains($path, 'pageadminmomentum')
        || str_contains($path, 'page_momentum')
        || str_contains($path, 'pagemomentum');

    if (!$pageMomentum) {
        return false;
    }

    $processTokens = [
        'candidate', 'preview', 'patchdraft', 'patch-draft', 'integrationplan',
        'integration-plan', 'preflight', 'cutover', 'hookconsumer', 'hook-consumer',
        'sourcehook', 'source-hook', 'route-menu-hook', 'runtime-candidate',
        'hook-candidate', 'closure', 'readiness', 'prove-', 'generate-', 'write-',
        'plan', 'phase-1.', 'phase-14', 'phase-15', 'phase-16',
    ];

    foreach ($processTokens as $token) {
        if (str_contains($path, $token) || str_contains($name, $token)) {
            return true;
        }
    }

    return false;
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
