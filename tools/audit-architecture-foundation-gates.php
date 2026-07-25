<?php

declare(strict_types=1);

/**
 * Architecture foundation gate aggregator.
 *
 * Read-only audit that checks the permanent architecture guard set exists and
 * that core runtime source remains free of direct feature-module dependencies.
 *
 * Temporary fixer/hotfix artefacts are warned about, while intentionally durable
 * tools are allowlisted through config/durable_tools.php.
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
    'tools/audit-durable-tool-registry.php',
    'app/zoosper-core/tests/Unit/Architecture/SiteLookupBoundaryRegressionTest.php',
    'app/zoosper-core/tests/Unit/Architecture/SiteLookupServiceBindingRegressionTest.php',
    'app/zoosper-core/tests/Unit/Architecture/DurableToolRegistryTest.php',
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

$durableToolAllowlist = loadDurableToolAllowlist($root, $warnings);

$temporaryArtefacts = [];
$temporaryPatterns = [
    '/^tools\/fix-.*\.php$/',
    '/^tools\/apply-.*cutover.*\.php$/',
    '/^docs\/development\/.*hotfix.*\.md$/',
    '/^docs\/development\/.*-v[0-9]+.*\.md$/',
    '/^docs\/roadmap\/.*-v[0-9]+\.md$/',
];

foreach (['tools', 'docs/development', 'docs/roadmap'] as $relativeDir) {
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
        if (in_array($relative, $durableToolAllowlist, true)) {
            continue;
        }

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
$observations[] = 'Durable tool allowlist entries loaded: ' . count($durableToolAllowlist);
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
    'durableToolAllowlist' => $durableToolAllowlist,
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
$report[] = '';
$report[] = '### Durable tool allowlist';
foreach ($durableToolAllowlist as $tool) {
    $report[] = '- ' . $tool;
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

/**
 * @return list<string>
 */
function loadDurableToolAllowlist(string $root, array &$warnings): array
{
    $registryFile = $root . '/config/durable_tools.php';
    if (!is_file($registryFile)) {
        $warnings[] = 'Durable tool registry not found. Temporary artefact detection will use an empty allowlist.';
        return [];
    }

    $registry = require $registryFile;
    if (!is_array($registry)) {
        $warnings[] = 'Durable tool registry did not return an array. Temporary artefact detection will use an empty allowlist.';
        return [];
    }

    $allowlist = [];
    foreach ($registry as $tool => $metadata) {
        if (is_string($tool) && str_starts_with($tool, 'tools/') && !str_contains($tool, '..')) {
            $allowlist[] = $tool;
        }
    }

    return array_values(array_unique($allowlist));
}
