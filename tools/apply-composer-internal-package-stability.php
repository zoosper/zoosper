<?php

declare(strict_types=1);

/**
 * Guarded repair tool for Composer internal package stability.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
$apply = false;
foreach ($argv as $argument) {
    if ($argument === '--apply') {
        $apply = true;
        continue;
    }
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$composerPath = $root . DIRECTORY_SEPARATOR . 'composer.json';
$errors = [];
$actions = [];

if (! is_file($composerPath)) {
    $errors[] = 'Root composer.json was not found.';
}

$data = is_file($composerPath) ? json_decode((string) file_get_contents($composerPath), true) : null;
if (! is_array($data)) {
    $errors[] = 'Root composer.json could not be decoded.';
    $data = [];
}

$packages = discoverLocalComposerPackages($root);
$requiredNames = requiredPackageNames($data, $packages);
$packageByName = [];
foreach ($packages as $path => $packageData) {
    $packageByName[(string) $packageData['name']] = $path;
}

$newData = $data;

if (($newData['minimum-stability'] ?? null) !== 'dev') {
    $newData['minimum-stability'] = 'dev';
    $actions[] = 'Set minimum-stability to dev.';
}

if (($newData['prefer-stable'] ?? null) !== true) {
    $newData['prefer-stable'] = true;
    $actions[] = 'Set prefer-stable to true.';
}

if (! isset($newData['require']) || ! is_array($newData['require'])) {
    $newData['require'] = [];
}

$addedRequirements = [];
foreach ($requiredNames as $name) {
    if (! str_starts_with($name, 'zoosper/')) {
        continue;
    }

    if (($newData['name'] ?? null) === $name) {
        continue;
    }

    if (isset($packageByName[$name]) && ! isset($newData['require'][$name]) && ! isset(($newData['require-dev'] ?? [])[$name])) {
        $newData['require'][$name] = '*@dev';
        $addedRequirements[$name] = $packageByName[$name];
        $actions[] = 'Add explicit root requirement for ' . $name . ' *@dev.';
    }
}

ksort($newData['require']);

if ($apply && $errors === []) {
    if ($newData !== $data) {
        $backupPath = $composerPath . '.phase-1.39-stability.bak';
        copy($composerPath, $backupPath);
        file_put_contents($composerPath, json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        $actions[] = 'Wrote root composer.json and backup ' . basename($backupPath);
    } else {
        $actions[] = 'No composer.json changes were required.';
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer-internal-package-stability.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer-internal-package-stability.log';

$report = [];
$report[] = '# Composer Internal Package Stability Repair';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
$report[] = 'Current minimum-stability: ' . (string) ($data['minimum-stability'] ?? 'not set');
$report[] = 'Target minimum-stability: ' . (string) ($newData['minimum-stability'] ?? 'not set');
$report[] = 'Current prefer-stable: ' . json_encode($data['prefer-stable'] ?? null);
$report[] = 'Target prefer-stable: ' . json_encode($newData['prefer-stable'] ?? null);
$report[] = 'Added root internal requirements: ' . count($addedRequirements);
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Added root requirements';
if ($addedRequirements === []) {
    $report[] = '- none';
} else {
    foreach ($addedRequirements as $name => $path) {
        $report[] = '- ' . $name . ' *@dev => ' . $path;
    }
}

if ($actions !== []) {
    $report[] = '';
    $report[] = '## Actions';
    foreach ($actions as $action) {
        $report[] = '- ' . $action;
    }
}

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Composer internal package stability report written to: ' . $reportPath;
$log[] = 'MODE ' . ($apply ? 'apply' : 'dry-run');
$log[] = 'TARGET_MINIMUM_STABILITY ' . (string) ($newData['minimum-stability'] ?? 'not set');
$log[] = 'TARGET_PREFER_STABLE ' . json_encode($newData['prefer-stable'] ?? null);
$log[] = 'ADDED_INTERNAL_REQUIREMENTS ' . count($addedRequirements);
$log[] = 'COMPOSER_STABILITY_REPAIR_ERRORS ' . count($errors);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);

/** @return array<string,array<string,mixed>> */
function discoverLocalComposerPackages(string $root): array
{
    $packages = [];
    foreach (['app', 'packages'] as $base) {
        $directory = $root . DIRECTORY_SEPARATOR . $base;
        if (! is_dir($directory)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getFilename() !== 'composer.json') {
                continue;
            }
            $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $data = json_decode((string) file_get_contents($file->getPathname()), true);
            if (is_array($data) && isset($data['name'])) {
                $packages[dirname($relative)] = $data;
            }
        }
    }
    ksort($packages);
    return $packages;
}

/** @param array<string,mixed> $rootData @param array<string,array<string,mixed>> $packages @return list<string> */
function requiredPackageNames(array $rootData, array $packages): array
{
    $names = [];
    foreach ([$rootData, ...array_values($packages)] as $data) {
        foreach (['require', 'require-dev'] as $section) {
            foreach (($data[$section] ?? []) as $name => $_constraint) {
                $names[] = (string) $name;
            }
        }
    }
    return array_values(array_unique($names));
}
