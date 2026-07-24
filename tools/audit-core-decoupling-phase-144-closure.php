<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$errors = 0;
$warnings = 0;
$report = [];

$requiredFiles = [
    'tools/audit-core-downstream-module-dependencies.php',
    'tools/plan-core-decoupling-phase-144.php',
    'tools/audit-core-decoupling-contracts.php',
    'tools/audit-feature-module-decoupling-adapters.php',
    'tools/audit-core-downstream-after-phase-144.php',
    'tools/audit-core-decoupling-phase-144-closure.php',
    'docs/development/core-decoupling-phase-1.44.md',
    'docs/development/core-decoupling-contracts-phase-1.44.md',
    'docs/development/core-decoupling-phase-1.44-closure.md',
];

$report[] = '## Phase 1.44 Core Decoupling Readiness Closure Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredFiles as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$contracts = [
    'Zoosper\\Core\\Routing\\FallbackHandlerInterface',
    'Zoosper\\Core\\Routing\\NullFallbackHandler',
    'Zoosper\\Page\\Routing\\PageFallbackHandlerAdapter',
    'Zoosper\\Core\\Site\\SiteContextProviderInterface',
    'Zoosper\\Core\\Site\\NullSiteContextProvider',
    'Zoosper\\Site\\Site\\SiteContextProviderAdapter',
];

$report[] = '';
$report[] = '### Contract and adapter symbols';
foreach ($contracts as $symbol) {
    $exists = class_exists($symbol) || interface_exists($symbol);
    $report[] = '- ' . $symbol . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Runtime fallback rewired: no';
$report[] = 'Runtime site context binding changed: no';
$report[] = 'Remaining downstream references expected: yes';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/core-decoupling-phase-144-closure.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/core-decoupling-phase-144-closure.log', "CORE_DECOUPLING_PHASE_144_CLOSURE_WARNINGS {$warnings}\nCORE_DECOUPLING_PHASE_144_CLOSURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
