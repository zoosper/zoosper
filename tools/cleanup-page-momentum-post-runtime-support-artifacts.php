<?php

declare(strict_types=1);

/**
 * Page Momentum post-runtime support cleanup helper.
 *
 * This tool scans docs/tools only. It targets support artefacts that reference
 * runtime candidate/scaffolding classes removed by the Phase 1.65m-z runtime
 * consolidation. It does not touch app runtime source.
 */

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);
$timestamp = gmdate('Ymd-His');
$reportDir = $root . '/var/reports';
$quarantineRoot = $root . '/var/quarantine/page-momentum-post-runtime-support-artifacts/' . $timestamp;

$scanScopes = [
    $root . '/tools',
    $root . '/docs/development',
    $root . '/docs/roadmap',
];

$removedRuntimeSymbols = [
    'PageMomentumActivationGuard',
    'PageMomentumAdminAggregationBridge',
    'PageMomentumAdminHookProvider',
    'PageMomentumAdminLiveAggregationIntegrator',
    'PageMomentumAdminMenuBridge',
    'PageMomentumAdminRouteBridge',
    'PageMomentumAdminRouteMenuHook',
    'PageMomentumAdminRuntimeAggregationProvider',
    'PageMomentumAdminSourceHookAdapter',
    'PageMomentumAggregatorPatchBuilder',
    'PageMomentumDefinitionProvider',
    'PageMomentumLiveDuplicateGuard',
    'PageMomentumMenuDefinitionProvider',
    'PageMomentumRouteDefinitionProvider',
    'PageMomentumRuntimeBridge',
];

$removedRuntimeConfigNames = [
    'admin_page_momentum_hook_candidate',
    'admin_page_momentum_route_menu_hook',
    'admin_page_momentum_runtime_aggregation_candidate',
    'admin_page_momentum_runtime_candidate',
    'admin_page_momentum_source_hook_adapter',
];

$keepBasenames = [
    // Cleanup/hygiene tools and their docs.
    'cleanup-page-momentum-process-artifacts.php',
    'cleanup-page-momentum-support-artifacts.php',
    'cleanup-page-momentum-post-runtime-support-artifacts.php',
    'audit-page-momentum-runtime-dependencies.php',
    'plan-page-momentum-runtime-consolidation.php',
    'audit-repository-lean-hygiene.php',
    'audit-repository-file-count-baseline.php',
    'page-momentum-process-debt-cleanup-runtime-guard.md',
    'page-momentum-support-artifact-cleanup.md',
    'page-momentum-runtime-dependency-audit.md',
    'page-momentum-runtime-consolidation-plan.md',
    'page-momentum-runtime-consolidation-planner-fix.md',
    'page-momentum-runtime-consolidation-test-hotfix.md',
    'page-momentum-route-metadata-test-escape-hotfix.md',
    'page-momentum-post-runtime-support-cleanup.md',
    'repository-lean-hygiene.md',
    'repository-lean-hygiene-scope-fix.md',
    'repository-file-count-baseline.md',

    // Current durable dashboard/facts docs/tools.
    'page-momentum-dashboard-facts-provider.md',
    'page-admin-dashboard-facts.md',
    'page-admin-dashboard-facts-closure.md',
    'page-admin-dashboard-status-system.md',
    'page-admin-dashboard-status-system-closure.md',
    'page-admin-dashboard-visual-shell.md',
    'page-admin-dashboard-indicators.md',
    'page-admin-dashboard-indicator-rendering.md',
    'page-admin-launch-readiness-dashboard.md',
    'page-admin-launch-readiness-dashboard-closure.md',
    'audit-page-momentum-dashboard-facts-provider.php',
    'smoke-page-admin-dashboard-facts.php',
    'audit-page-admin-dashboard-facts.php',
    'audit-page-admin-dashboard-facts-closure.php',
    'smoke-page-admin-dashboard-status-system.php',
    'audit-page-admin-dashboard-status-system.php',
    'audit-page-admin-dashboard-status-system-closure.php',
    'smoke-page-admin-dashboard-visual-shell.php',
    'smoke-page-admin-dashboard-indicators.php',
    'audit-page-admin-dashboard-indicators.php',
    'smoke-page-admin-dashboard-indicator-rendering.php',
    'audit-page-admin-dashboard-indicator-rendering.php',
    'smoke-page-admin-launch-readiness-dashboard.php',
    'audit-page-admin-launch-readiness-dashboard.php',
    'audit-page-admin-launch-readiness-dashboard-invariants.php',
];

$matches = [];
foreach (listFiles($root, $scanScopes) as $relative => $path) {
    if (in_array(basename($relative), $keepBasenames, true)) {
        continue;
    }

    if (!isPageMomentumSupportFile($relative)) {
        continue;
    }

    $contents = @file_get_contents($path);
    if ($contents === false) {
        continue;
    }

    $reason = matchRemovedRuntimeReference($relative, $contents, $removedRuntimeSymbols, $removedRuntimeConfigNames);
    if ($reason !== null) {
        $matches[$relative] = $reason;
    }
}
ksort($matches);

$moved = [];
$errors = [];
if ($apply) {
    if (!is_dir($quarantineRoot)) {
        mkdir($quarantineRoot, 0775, true);
    }

    foreach (array_keys($matches) as $relative) {
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
    'matched' => $matches,
    'moved' => $moved,
    'errors' => $errors,
    'quarantine' => $apply ? str_replace($root . '/', '', $quarantineRoot) : null,
];

$report = [];
$report[] = '## Page Momentum Post-Runtime Support Cleanup';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = 'Mode: ' . $payload['mode'];
$report[] = '';
$report[] = 'Matched support artefacts: ' . count($matches);
$report[] = 'Moved support artefacts: ' . count($moved);
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '### Matched support files';
foreach ($matches as $relative => $reason) {
    $report[] = '- ' . $relative;
    $report[] = '  - reason: ' . $reason;
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

file_put_contents($reportDir . '/page-momentum-post-runtime-support-cleanup.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-momentum-post-runtime-support-cleanup.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);

function isPageMomentumSupportFile(string $relative): bool
{
    if (!(str_starts_with($relative, 'tools/') || str_starts_with($relative, 'docs/'))) {
        return false;
    }

    return preg_match('/page-admin-momentum|page-momentum|pagemomentum|page_momentum/i', $relative) === 1;
}

/**
 * @param list<string> $symbols
 * @param list<string> $configNames
 */
function matchRemovedRuntimeReference(string $relative, string $contents, array $symbols, array $configNames): ?string
{
    $lowerRelative = strtolower($relative);
    $lowerContents = strtolower($contents);

    foreach ($symbols as $symbol) {
        if (str_contains($contents, $symbol) || str_contains($lowerRelative, strtolower($symbol))) {
            return 'references removed runtime candidate symbol ' . $symbol;
        }
    }

    foreach ($configNames as $name) {
        if (str_contains($lowerContents, strtolower($name)) || str_contains($lowerRelative, strtolower($name))) {
            return 'references removed runtime candidate config ' . $name;
        }
    }

    if (preg_match('/(runtime-candidate|runtime-aggregation|route-menu-hook|source-hook|hook-candidate|aggregation-bridge|runtime-bridge|live-aggregation)/i', $relative) === 1) {
        return 'filename indicates removed runtime consolidation support artefact';
    }

    return null;
}

/**
 * @param list<string> $scopes
 * @return array<string,string>
 */
function listFiles(string $root, array $scopes): array
{
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
