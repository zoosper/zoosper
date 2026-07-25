<?php

declare(strict_types=1);

/**
 * Guarded ApplicationFactory fallback-boundary cutover patcher.
 *
 * Dry-run by default. With --apply, replaces the direct PageController import
 * and service lookup with the core-owned fallback handler boundary.
 */

$root = dirname(__DIR__);
$apply = in_array('--apply', $argv, true);
$timestamp = gmdate('Ymd-His');
$targetRelative = 'app/zoosper-core/src/Bootstrap/ApplicationFactory.php';
$target = $root . '/' . $targetRelative;
$reportDir = $root . '/var/reports';
$backupDir = $root . '/var/backups/application-factory-fallback-cutover/' . $timestamp;

$errors = [];
$warnings = [];
$changes = [];
$observations = [];

$removeImport = 'use Zoosper\\Page\\Controller\\PageController;';
$addImports = [
    'use Zoosper\\Core\\Routing\\FallbackHandlerInterface;',
    'use Zoosper\\Core\\Routing\\NullFallbackHandler;',
];
$oldLookup = '$pageController = $services->get(PageController::class);';
$newLookup = <<<'PHP_CODE'
$fallbackHandler = $services->has(FallbackHandlerInterface::class)
            ? $services->get(FallbackHandlerInterface::class)
            : new NullFallbackHandler();
PHP_CODE;

if (!is_file($target)) {
    $errors[] = 'Missing target file: ' . $targetRelative;
} else {
    $source = (string) file_get_contents($target);
    $original = $source;

    $observations[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
    $observations[] = 'Target: ' . $targetRelative;
    $observations[] = 'PageController token count before: ' . substr_count($source, 'PageController');
    $observations[] = '$pageController variable count before: ' . substr_count($source, '$pageController');

    if (!str_contains($source, $removeImport)) {
        $errors[] = 'Expected PageController import was not found.';
    }
    if (!str_contains($source, $oldLookup)) {
        $errors[] = 'Expected PageController service lookup was not found: ' . $oldLookup;
    }

    if ($errors === []) {
        $source = str_replace($removeImport . "\n", '', $source, $countImportWithNewline);
        if ($countImportWithNewline === 0) {
            $source = str_replace($removeImport, '', $source, $countImportWithoutNewline);
        }
        $changes[] = 'Removed direct PageController import.';

        foreach ($addImports as $import) {
            if (!str_contains($source, $import)) {
                $source = insertImport($source, $import);
                $changes[] = 'Added import: ' . $import;
            }
        }

        $source = str_replace($oldLookup, $newLookup, $source, $lookupCount);
        if ($lookupCount !== 1) {
            $errors[] = 'Expected to replace exactly one PageController service lookup, replaced: ' . $lookupCount;
        } else {
            $changes[] = 'Replaced PageController service lookup with fallback handler boundary resolution.';
        }

        // Rename the local variable after replacing the assignment. This keeps
        // downstream constructor/argument usage aligned with fallback semantics.
        $source = str_replace('$pageController', '$fallbackHandler', $source, $variableRenameCount);
        $changes[] = 'Renamed local $pageController variable occurrences to $fallbackHandler: ' . $variableRenameCount;

        $observations[] = 'PageController token count after: ' . substr_count($source, 'PageController');
        $observations[] = '$fallbackHandler variable count after: ' . substr_count($source, '$fallbackHandler');

        if (str_contains($source, 'Zoosper\\Page\\Controller\\PageController')) {
            $errors[] = 'Direct PageController namespace still remains after planned patch.';
        }

        if ($apply && $errors === []) {
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0775, true);
            }
            file_put_contents($backupDir . '/ApplicationFactory.php.before', $original);
            file_put_contents($target, $source);
            $changes[] = 'Applied patch and wrote backup to ' . str_replace($root . '/', '', $backupDir);
        }
    }
}

if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

$payload = [
    'generatedAt' => gmdate('c'),
    'mode' => $apply ? 'apply' : 'dry-run',
    'target' => $targetRelative,
    'errors' => $errors,
    'warnings' => $warnings,
    'changes' => $changes,
    'observations' => $observations,
    'backup' => $apply && $errors === [] ? str_replace($root . '/', '', $backupDir) : null,
];

$report = [];
$report[] = '## ApplicationFactory Fallback Cutover Apply';
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

file_put_contents($reportDir . '/application-factory-fallback-cutover-apply.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/application-factory-fallback-cutover-apply.json', json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo implode("\n", $report) . "\n";
exit($errors === [] ? 0 : 1);

function insertImport(string $source, string $import): string
{
    $lines = preg_split('/\R/', $source);
    if ($lines === false) {
        return $source . "\n" . $import . "\n";
    }

    $lastUseIndex = null;
    foreach ($lines as $index => $line) {
        if (str_starts_with(trim($line), 'use ')) {
            $lastUseIndex = $index;
        }
    }

    if ($lastUseIndex === null) {
        array_splice($lines, 1, 0, [$import]);
    } else {
        array_splice($lines, $lastUseIndex + 1, 0, [$import]);
    }

    return implode("\n", $lines);
}
