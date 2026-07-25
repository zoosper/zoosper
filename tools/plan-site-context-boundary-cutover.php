<?php

declare(strict_types=1);

/**
 * Site context boundary cutover planner.
 *
 * Read-only planner that writes a human-reviewed remediation draft for replacing
 * direct core imports of SiteRepository/DbSite with core-owned lookup contracts.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$readinessJson = $reportDir . '/site-context-boundary-readiness.json';
$errors = [];
$warnings = [];
$observations = [];

if (!is_file($readinessJson)) {
    $errors[] = 'Missing site-context-boundary-readiness.json. Run tools/audit-site-context-boundary-readiness.php first.';
    $findings = [];
} else {
    $payload = json_decode((string) file_get_contents($readinessJson), true);
    $findings = is_array($payload) && isset($payload['findings']) && is_array($payload['findings'])
        ? $payload['findings']
        : [];
}

$plan = [
    'contractCandidates' => [
        'Zoosper\\Core\\Site\\SiteLookupInterface' => 'Core-owned lookup contract for resolving site records/details without importing Site module repository or model classes.',
        'Zoosper\\Core\\Site\\ResolvedSite' => 'Core-owned immutable DTO/value object containing only fields core needs for request site context.',
        'Zoosper\\Core\\Site\\NullSiteLookup' => 'Safe no-op implementation for fallback/bootstrap cases.',
    ],
    'siteModuleAdapterCandidates' => [
        'Zoosper\\Site\\Infrastructure\\DatabaseSiteLookup' => 'Site-module adapter backed by SiteRepository. This adapter can import SiteRepository because it lives in the Site module.',
    ],
    'cutoverSteps' => [
        'Add core-owned SiteLookupInterface and ResolvedSite DTO.',
        'Add NullSiteLookup safe implementation in core.',
        'Add Site module DatabaseSiteLookup adapter backed by SiteRepository.',
        'Update SiteContextResolverFactory to accept/resolve SiteLookupInterface instead of SiteRepository.',
        'Update SiteContextResolver to consume ResolvedSite/core DTO instead of DbSite.',
        'Run audit-core-feature-coupling.php and confirm Site module references decrease.',
    ],
    'riskNotes' => [
        'Avoid changing request site isolation semantics.',
        'Avoid reintroducing global CurrentSiteContext fallback usage.',
        'Preserve siteId population in SiteContext.',
        'Keep runtime behaviour identical before removing old imports.',
    ],
    'findings' => $findings,
];

$observations[] = 'Prepared contract-first cutover plan for Site context boundary.';
$observations[] = 'This phase does not edit runtime PHP files.';

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$report = [];
$report[] = '## Site Context Boundary Cutover Plan';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = 'Observations: ' . count($observations);
$report[] = '';
$report[] = '### Contract candidates';
foreach ($plan['contractCandidates'] as $class => $description) {
    $report[] = '- ' . $class;
    $report[] = '  - ' . $description;
}
$report[] = '';
$report[] = '### Site module adapter candidates';
foreach ($plan['siteModuleAdapterCandidates'] as $class => $description) {
    $report[] = '- ' . $class;
    $report[] = '  - ' . $description;
}
$report[] = '';
$report[] = '### Suggested cutover steps';
foreach ($plan['cutoverSteps'] as $step) {
    $report[] = '- ' . $step;
}
$report[] = '';
$report[] = '### Risk notes';
foreach ($plan['riskNotes'] as $note) {
    $report[] = '- ' . $note;
}
$report[] = '';
$report[] = '### Observations';
foreach ($observations as $observation) {
    $report[] = '- ' . $observation;
}
if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportDir . '/site-context-boundary-cutover-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/site-context-boundary-cutover-plan.json', json_encode([
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
    'plan' => $plan,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
