<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];

$expected = [
    'var/reports/site-context-runtime-cutover-plan.txt',
    'var/reports/site-context-runtime-cutover-plan.json',
    'var/reports/site-context-runtime-cutover-draft.patch.md',
];

foreach ($expected as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing expected Site context runtime cutover plan output: ' . $file;
    }
}

$draft = $root . '/var/reports/site-context-runtime-cutover-draft.patch.md';
if (is_file($draft)) {
    $source = (string) file_get_contents($draft);
    foreach (['SiteLookupInterface', 'ResolvedSite', 'NullSiteLookup', 'SiteRepository', 'DbSite'] as $needle) {
        if (!str_contains($source, $needle)) {
            $errors[] = 'Draft is missing expected token: ' . $needle;
        }
    }
} else {
    $warnings[] = 'Draft missing. Run plan-site-context-runtime-cutover.php first.';
}

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$report = [];
$report[] = '## Site Context Runtime Cutover Plan Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = '';
$report[] = '### Errors';
foreach ($errors as $error) {
    $report[] = '- ' . $error;
}
$report[] = '';
$report[] = '### Warnings';
foreach ($warnings as $warning) {
    $report[] = '- ' . $warning;
}

file_put_contents($reportDir . '/site-context-runtime-cutover-plan-audit.txt', implode("\n", $report) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
