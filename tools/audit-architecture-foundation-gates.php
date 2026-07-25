<?php

declare(strict_types=1);

/**
 * Architecture foundation gate aggregator.
 *
 * Read-only audit that checks the permanent architecture guard set exists and
 * that core runtime source remains free of direct feature-module dependencies.
 * It also warns about temporary hotfix/fixer artefacts so the repository stays
 * lean after heavy architecture work.
 */

$root = dirname(__DIR__);
$reportDir = $root . '/var/reports';
$errors = [];
$warnings = [];
$observations = [];

$requiredGuards = [
    'tools/audit-core-feature-coupling.php',
    'tools/audit-core-feature-decoupling-closure.php',
    'tools/audit-site-lookup-boundary-regression.php',
    'tools/audit-site-lookup-service-binding.php',
    'tools/audit-site-lookup-service-binding-regression.php',
    'app/zoosper-core/tests/Unit/Architecture/SiteLookupBoundaryRegressionTest.php',
    'app/zoosper-core/tests/Unit/Architecture/SiteLookupServiceBindingRegressionTest.php',
];

foreach ($requiredGuards as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing required architecture guard: ' . $file;
    }
}

$coreSourceDir = $root . '/app/zoosper-core/src';
$forbiddenFeatureNamespaces = [
    'Zoosper\\Page\\',
    'Zoosper\\Site\\',
    'Zoosper\\Auth\\',
    'Zoosper\\Theme\\',
    'Zoosper\\Media\\',
    'Zoosper\\Admin\\',
    'Zoosper\\Api\\',
];

$coreViolations = [];
$coreFileCount = 0;
if (is_dir($coreSourceDir)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($coreSourceDir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $coreFileCount++;
        $relative = str_replace($root . '/', '', $file->getPathname());
        $source = (string) file_get_contents($file->getPathname());

        foreach ($forbiddenFeatureNamespaces as $needle) {
            if (str_contains($source, $needle)) {
                $coreViolations[] = $relative . ' contains ' . $needle;
            }
        }
    }
} else {
    $errors[] = 'Missing core source directory: app/zoosper-core/src';
}

foreach ($coreViolations as $violation) {
    $errors[] = 'Core feature coupling regression: ' . $violation;
}

$temporaryArtefacts = [];
$temporaryPatterns = [
    '/^tools\/fix-.*\.php$/',
    '/^tools\/apply-.*cutover.*\.php$/',
    '/^docs\/development\/.*hotfix.*\.md$/',
    '/^docs\/development\/.*-v[0-9]+.*\.md$/',
    '/^docs\/roadmap\/.*-v[0-9]+\.md$/',
];

$scanForTemporary = [
    'tools',
    'docs/development',
    'docs/roadmap',
];

foreach ($scanForTemporary as $relativeDir) {
    $dir = $root . '/' . $relativeDir;
    if (!is_dir($dir)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $relative = str_replace($root . '/', '', $file->getPathname());
        foreach ($temporaryPatterns as $pattern) {
            if (preg_match($pattern, $relative) === 1) {
                $temporaryArtefacts[] = $relative;
                break;
            }
        }
    }
}

$temporaryArtefacts = array_values(array_unique($temporaryArtefacts));
if ($temporaryArtefacts !== []) {
    $warnings[] = 'Temporary fixer/hotfix artefacts detected. Review before committing to keep the repository lean.';
}

$observations[] = 'Required architecture guards checked: ' . count($requiredGuards);
$observations[] = 'Core source PHP files scanned: ' . $coreFileCount;
$observations[] = 'Core feature coupling violations: ' . count($coreViolations);
$observations[] = 'Temporary fixer/hotfix artefacts detected: ' . count($temporaryArtefacts);

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'errors' => $errors,
    'warnings' => $warnings,
    'observations' => $observations,
    'coreViolations' => $coreViolations,
    'temporaryArtefacts' => $temporaryArtefacts,
];

$report = [];
$report[] = '## Architecture Foundation Gates Audit';
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
if ($temporaryArtefacts !== []) {
    $report[] = '';
    $report[] = '### Temporary artefacts';
    foreach ($temporaryArtefacts as $artefact) {
        $report[] = '- ' . $artefact;
    }
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

file_put_contents($reportDir . '/architecture-foundation-gates.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/architecture-foundation-gates.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);
