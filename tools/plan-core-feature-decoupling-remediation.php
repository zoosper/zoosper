<?php

declare(strict_types=1);

/**
 * Core feature decoupling remediation planner.
 *
 * Reads the output from tools/audit-core-feature-coupling.php and groups direct
 * core-to-feature references into practical remediation buckets. This phase is
 * deliberately read-only and does not edit source files.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$auditJson = $reportDir . '/core-feature-coupling.json';

if (!is_file($auditJson)) {
    fwrite(STDERR, "Missing var/reports/core-feature-coupling.json. Run php8.5 tools/audit-core-feature-coupling.php first.\n");
    exit(1);
}

$audit = json_decode((string) file_get_contents($auditJson), true);
if (!is_array($audit)) {
    fwrite(STDERR, "Invalid core-feature-coupling.json.\n");
    exit(1);
}

$violations = $audit['violations'] ?? [];
if (!is_array($violations)) {
    $violations = [];
}

$plans = [];
foreach ($violations as $violation) {
    if (!is_array($violation)) {
        continue;
    }

    $module = (string) ($violation['module'] ?? 'Unknown module');
    $file = (string) ($violation['file'] ?? 'unknown');
    $source = (string) ($violation['source'] ?? '');
    $boundary = classifyBoundary($module, $source, $file);

    $plans[$module][$boundary][] = [
        'file' => $file,
        'line' => (int) ($violation['line'] ?? 0),
        'source' => $source,
        'recommendation' => recommendationFor($module, $boundary),
    ];
}
ksort($plans);
foreach ($plans as &$modulePlans) {
    ksort($modulePlans);
}
unset($modulePlans);

$summary = [
    'violationCount' => count($violations),
    'moduleCount' => count($plans),
    'boundaryCount' => array_sum(array_map('count', $plans)),
];

$payload = [
    'generatedAt' => gmdate('c'),
    'summary' => $summary,
    'plans' => $plans,
];

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$report = [];
$report[] = '## Core Feature Decoupling Remediation Plan';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = '';
$report[] = '### Summary';
foreach ($summary as $key => $value) {
    $report[] = '- ' . $key . ': ' . $value;
}

if ($plans === []) {
    $report[] = '';
    $report[] = 'No core-to-feature coupling violations were found in the latest audit.';
} else {
    foreach ($plans as $module => $modulePlans) {
        $report[] = '';
        $report[] = '### ' . $module;
        foreach ($modulePlans as $boundary => $items) {
            $report[] = '';
            $report[] = '#### ' . $boundary;
            $report[] = '- Findings: ' . count($items);
            $report[] = '- Recommended remediation: ' . recommendationFor($module, $boundary);
            foreach ($items as $item) {
                $report[] = '  - ' . $item['file'] . ':' . $item['line'];
                $report[] = '    - ' . $item['source'];
            }
        }
    }
}

file_put_contents($reportDir . '/core-feature-decoupling-remediation-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/core-feature-decoupling-remediation-plan.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit(0);

function classifyBoundary(string $module, string $source, string $file): string
{
    $lower = strtolower($source . ' ' . $file);

    if (str_contains($lower, 'controller') || str_contains($lower, 'fallback')) {
        return 'Fallback handler boundary';
    }

    if (str_contains($lower, 'sitecontext') || str_contains($lower, 'siteresolver') || str_contains($lower, 'site')) {
        return 'Site context boundary';
    }

    if (str_contains($lower, 'auth') || str_contains($lower, 'guard') || str_contains($lower, 'session')) {
        return 'Authentication boundary';
    }

    if (str_contains($lower, 'theme')) {
        return 'Theme resolution boundary';
    }

    if (str_contains($lower, 'media')) {
        return 'Media service boundary';
    }

    return 'General module contract boundary';
}

function recommendationFor(string $module, string $boundary): string
{
    return match ($boundary) {
        'Fallback handler boundary' => 'Move the feature implementation behind a core-owned interface such as FallbackHandlerInterface and bind the concrete handler from the feature module service config.',
        'Site context boundary' => 'Introduce or use a core-owned site context resolver interface, then bind the site-module implementation from the Site module instead of importing Site classes in core.',
        'Authentication boundary' => 'Route auth-specific runtime behaviour through a core-owned guard/session contract and bind concrete Auth implementations from the Auth module.',
        'Theme resolution boundary' => 'Replace direct theme-module imports with a core-owned theme resolution contract and feature-module binding.',
        'Media service boundary' => 'Replace direct media-module imports with a core-owned media service contract and feature-module binding.',
        default => 'Introduce a narrow core-owned contract for the dependency, then bind the module implementation from the owning feature module.',
    };
}
