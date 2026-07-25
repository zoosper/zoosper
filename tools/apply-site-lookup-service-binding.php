<?php

declare(strict_types=1);

/**
 * Guarded SiteLookupInterface service binding patcher v3.
 *
 * Dry-run by default. Uses fully-qualified class names in the Site module
 * services config to avoid fragile import insertion and validation drift.
 */

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);
$timestamp = gmdate('Ymd-His');
$reportDir = $root . '/var/reports';
$backupDir = $root . '/var/backups/site-lookup-service-binding/' . $timestamp;
$targetRelative = 'app/zoosper-site/config/services.php';
$target = $root . '/' . $targetRelative;

$errors = [];
$warnings = [];
$changes = [];
$observations = [];

$required = [
    'app/zoosper-core/src/Site/SiteLookupInterface.php',
    'app/zoosper-site/src/Infrastructure/DatabaseSiteLookup.php',
    'app/zoosper-site/src/Repository/SiteRepository.php',
];

foreach ($required as $file) {
    if (!is_file($root . '/' . $file)) {
        $errors[] = 'Missing required file: ' . $file;
    }
}

if (!is_file($target)) {
    $errors[] = 'Missing Site module services file: ' . $targetRelative;
    $source = '';
    $original = '';
} else {
    $original = (string) file_get_contents($target);
    $source = $original;
}

if ($errors === []) {
    $bindingAlreadyPresent = str_contains($source, 'SiteLookupInterface::class')
        && str_contains($source, 'DatabaseSiteLookup');

    if ($bindingAlreadyPresent) {
        $observations[] = 'Site lookup binding already appears in ' . $targetRelative . '.';
    } else {
        $binding = <<<'PHP_BINDING'
    \\Zoosper\\Core\\Site\\SiteLookupInterface::class => static function ($container): \\Zoosper\\Site\\Infrastructure\\DatabaseSiteLookup {
        return new \\Zoosper\\Site\\Infrastructure\\DatabaseSiteLookup(
            $container->get(\\Zoosper\\Site\\Repository\\SiteRepository::class)
        );
    },
PHP_BINDING;

        $inserted = false;

        $returnShort = 'return [';
        $returnArray = 'return array(';

        if (str_contains($source, $returnShort)) {
            $source = insertAfterReturnOpening($source, $returnShort, $binding);
            $inserted = true;
        } elseif (str_contains($source, $returnArray)) {
            $source = insertAfterReturnOpening($source, $returnArray, $binding);
            $inserted = true;
        } else {
            $errors[] = 'Could not find a recognised return array shape in ' . $targetRelative . '. No changes were written.';
        }

        if ($inserted) {
            $changes[] = 'Inserted fully-qualified SiteLookupInterface -> DatabaseSiteLookup binding into ' . $targetRelative . '.';
        }
    }

    foreach (['SiteLookupInterface::class', 'DatabaseSiteLookup', 'SiteRepository::class'] as $needle) {
        if (!str_contains($source, $needle)) {
            $errors[] = 'Planned service config missing expected token: ' . $needle;
        }
    }

    if ($apply && $errors === [] && $source !== $original) {
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0775, true);
        }
        file_put_contents($backupDir . '/services.php.before', $original);
        file_put_contents($target, $source);
        $changes[] = 'Applied service binding patch and wrote backup.';
    }
}

$observations[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
$observations[] = 'Target: ' . $targetRelative;
$observations[] = 'Binding uses fully-qualified class names in the Site module config.';
$observations[] = 'NullSiteLookup remains the core fallback only.';

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'mode' => $apply ? 'apply' : 'dry-run',
    'errors' => $errors,
    'warnings' => $warnings,
    'changes' => $changes,
    'observations' => $observations,
    'backup' => $apply && $errors === [] ? str_replace($root . '/', '', $backupDir) : null,
];

$report = [];
$report[] = '## Site Lookup Service Binding Apply v3';
$report[] = '';
$report[] = 'Generated: ' . $payload['generatedAt'];
$report[] = 'Mode: ' . $payload['mode'];
$report[] = '';
$report[] = 'Errors: ' . count($errors);
$report[] = 'Warnings: ' . count($warnings);
$report[] = 'Changes: ' . count($changes);
$report[] = 'Observations: ' . count($observations);
$report[] = '';
$report[] = '### Changes';
foreach ($changes as $change) {
    $report[] = '- ' . $change;
}
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

file_put_contents($reportDir . '/site-lookup-service-binding-apply.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/site-lookup-service-binding-apply.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);

function insertAfterReturnOpening(string $source, string $needle, string $binding): string
{
    $position = strpos($source, $needle);
    if ($position === false) {
        return $source;
    }

    $insertAt = $position + strlen($needle);

    return substr($source, 0, $insertAt) . "\n" . $binding . substr($source, $insertAt);
}
