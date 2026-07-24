<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$auditFile = $root . '/var/reports/core-downstream-module-dependencies.json';
$errors = 0;
$report = [];

$report[] = '## Phase 1.44 Core Decoupling Plan';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($auditFile)) {
    $report[] = 'Audit JSON missing. Run tools/audit-core-downstream-module-dependencies.php first.';
    $errors++;
} else {
    $violations = json_decode((string) file_get_contents($auditFile), true);
    if (!is_array($violations)) {
        $report[] = 'Audit JSON could not be decoded.';
        $errors++;
    } else {
        $files = [];
        foreach ($violations as $violation) {
            if (is_array($violation) && isset($violation['file'])) {
                $files[(string) $violation['file']] = true;
            }
        }

        $report[] = 'Violation count: ' . count($violations);
        $report[] = 'Affected files: ' . count($files);
        $report[] = '';
        $report[] = '### Recommended decoupling sequence';
        $report[] = '1. Fallback route decoupling: introduce core-owned fallback handler/provider contract, then let zoosper-page register the catch-all fallback.';
        $report[] = '2. Site context decoupling: introduce core-owned site context resolver contract or provider seam, then bind zoosper-site implementation from the site module.';
        $report[] = '3. Console decoupling: keep bin/zoosper as a thin kernel and move feature commands into owning modules through the module console command loader.';
        $report[] = '4. Admin/API decoupling: move feature-specific admin/API controllers out of shell modules into owning feature modules in later phases.';
        $report[] = '';
        $report[] = '### Safety rule';
        $report[] = 'Do not remove existing concrete wiring until adapter/contract tests prove the replacement path. Each refactor should be reversible and covered by a runtime smoke/audit.';
    }
}

$report[] = '';
$report[] = 'Runtime changed: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/core-decoupling-phase-144-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/core-decoupling-phase-144-plan.log', "CORE_DECOUPLING_PHASE_144_PLAN_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
