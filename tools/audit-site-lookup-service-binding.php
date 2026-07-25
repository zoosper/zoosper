<?php

declare(strict_types=1);

/** Read-only audit for SiteLookupInterface service binding. */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];
$observations = [];

$siteServices = $root . '/app/zoosper-site/config/services.php';
$coreSiteDir = $root . '/app/zoosper-core/src/Site';

if (!is_file($siteServices)) {
    $errors[] = 'Missing app/zoosper-site/config/services.php';
} else {
    $siteSource = (string) file_get_contents($siteServices);
    foreach (['SiteLookupInterface::class', 'DatabaseSiteLookup', 'SiteRepository::class'] as $needle) {
        if (!str_contains($siteSource, $needle)) {
            $errors[] = 'Site module services config missing expected token: ' . $needle;
        }
    }
}

$coreSiteViolations = [];
if (is_dir($coreSiteDir)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($coreSiteDir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $relative = str_replace($root . '/', '', $file->getPathname());
        $source = (string) file_get_contents($file->getPathname());
        foreach (['Zoosper\\Site\\', 'SiteRepository', 'DbSite'] as $needle) {
            if (str_contains($source, $needle)) {
                $coreSiteViolations[] = $relative . ' contains ' . $needle;
            }
        }
    }
}

foreach ($coreSiteViolations as $violation) {
    $errors[] = 'Core Site decoupling violation remains: ' . $violation;
}

$observations[] = 'Site services binding audit completed.';
$observations[] = 'Core Site violation count: ' . count($coreSiteViolations);

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
];

$report = [];
$report[] = '## Site Lookup Service Binding Audit';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = '';
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = 'Observations: ' . count($observations);
$report[] = '';
$report[] = '### Observations';
foreach ($observations as $observation) {
    $report[] = '- ' . $observation;
}
if ($warnings !== []) {
    $report[] = '';
    $report[] = '### Warnings';
    foreach ($warnings as $warning) {
        $report[] = '- ' . $warning;
    }
}
if ($errors !== []) {
    $report[] = '';
    $report[] = '### Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportDir . '/site-lookup-service-binding-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/site-lookup-service-binding-audit.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
