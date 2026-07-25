<?php

declare(strict_types=1);

/**
 * Cleans up Page Momentum support artefacts in docs/tools.
 *
 * This tool is intentionally narrower than the code cleanup tool:
 * - It never moves app/src runtime code.
 * - It only targets docs/tools support files.
 * - It keeps current live dashboard facts/status/visual-shell docs and tools.
 * - --apply quarantines instead of deleting.
 */

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);
$timestamp = gmdate('Ymd-His');
$quarantineRoot = $root . '/var/quarantine/page-momentum-support-artifacts/' . $timestamp;
$reportDir = $root . '/var/reports';

$scanScopes = [
    $root . '/tools',
    $root . '/docs/development',
    $root . '/docs/roadmap',
];

$keepBasenames = [
    // Current cleanup tooling.
    'cleanup-page-momentum-process-artifacts.php',
    'cleanup-page-momentum-support-artifacts.php',
    'page-momentum-process-debt-cleanup-runtime-guard.md',
    'page-momentum-support-artifact-cleanup.md',
    'roadmap-status-fragment-phase-1.63a-l.md',
    'roadmap-status-fragment-phase-1.63m-z.md',

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
foreach ($scanScopes as $scope) {
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

        if (isSupportArtefact($relative, $basename)) {
            $matches[] = $relative;
        }
    }
}

sort($matches);

$moved = [];
$errors = [];

if ($apply) {
    if (!is_dir($quarantineRoot)) {
        mkdir($quarantineRoot, 0775, true);
    }

    foreach ($matches as $relative) {
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
$report[] = '## Page Momentum Support Artefact Cleanup';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
$report[] = '';
$report[] = 'Matched support artefacts: ' . count($matches);
$report[] = 'Moved support artefacts: ' . count($moved);
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '### Matched support files';
foreach ($matches as $relative) {
    $report[] = '- ' . $relative;
}

if ($apply) {
    $report[] = '';
    $report[] = 'Quarantine: ' . str_replace($root . '/', '', $quarantineRoot);
    $report[] = 'Restore script: ' . str_replace($root . '/', '', $quarantineRoot . '/restore.sh');
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportDir . '/page-momentum-support-artifact-cleanup.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/page-momentum-support-artifact-cleanup.json', json_encode([
    'generatedAt' => gmdate('c'),
    'mode' => $apply ? 'apply' : 'dry-run',
    'matched' => $matches,
    'moved' => $moved,
    'errors' => $errors,
    'quarantine' => $apply ? str_replace($root . '/', '', $quarantineRoot) : null,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);

function isSupportArtefact(string $relative, string $basename): bool
{
    $path = strtolower($relative);
    $name = strtolower($basename);

    $isPageMomentum = str_contains($path, 'page-admin-momentum')
        || str_contains($path, 'page-momentum')
        || str_contains($path, 'page_admin_momentum')
        || str_contains($path, 'pagemomentum');

    if (!$isPageMomentum) {
        return false;
    }

    $supportTokens = [
        'phase-',
        'phase_',
        'closure',
        'readiness',
        'hotfix',
        'preflight',
        'cutover',
        'candidate',
        'preview',
        'patch-draft',
        'patchdraft',
        'integration-plan',
        'integrationplan',
        'runtime-aggregation-candidate',
        'source-hook',
        'route-menu-hook',
        'hook-candidate',
        'consumer-hook',
        'prove-',
        'generate-',
        'write-',
        'plan-',
        'aggregator-',
        'live-cutover',
    ];

    foreach ($supportTokens as $token) {
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
