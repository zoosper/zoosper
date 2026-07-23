<?php

declare(strict_types=1);

/**
 * Guarded repair tool for Composer local path repositories.
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
$existingPaths = repositoryPaths($data['repositories'] ?? []);
$requiredNames = requiredPackageNames($data, $packages);
$packageByName = [];
foreach ($packages as $path => $packageData) {
    $packageByName[(string) $packageData['name']] = $path;
}

$missingLocalRepositories = [];
foreach ($requiredNames as $name) {
    if (! str_starts_with($name, 'zoosper/')) {
        continue;
    }

    if (($data['name'] ?? null) === $name) {
        continue;
    }

    if (isset($packageByName[$name]) && ! pathCovered($packageByName[$name], $existingPaths)) {
        $missingLocalRepositories[$name] = $packageByName[$name];
    }
}

if (in_array('zoosper/core', $requiredNames, true) && ! isset($packageByName['zoosper/core']) && (($data['name'] ?? null) !== 'zoosper/core')) {
    $errors[] = 'A package requires zoosper/core, but no local composer package named zoosper/core was discovered.';
}

$newData = $data;
if ($missingLocalRepositories !== []) {
    $repositories = $newData['repositories'] ?? [];
    if (! is_array($repositories)) {
        $repositories = [];
    }

    foreach ($missingLocalRepositories as $name => $path) {
        $repositories[] = [
            'type' => 'path',
            'url' => './' . $path,
            'options' => ['symlink' => true],
        ];
        $actions[] = 'Add path repository for ' . $name . ' at ./' . $path;
    }

    $newData['repositories'] = $repositories;
}

if ($apply && $errors === []) {
    if ($newData !== $data) {
        $backupPath = $composerPath . '.phase-1.39-composer-path.bak';
        copy($composerPath, $backupPath);
        file_put_contents($composerPath, json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        $actions[] = 'Wrote root composer.json and backup ' . basename($backupPath);
    } else {
        $actions[] = 'No composer.json changes were required.';
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer-local-package-repair.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer-local-package-repair.log';

$report = [];
$report[] = '# Composer Local Package Repository Repair';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
$report[] = 'Missing local repositories: ' . count($missingLocalRepositories);
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Proposed repository additions';
if ($missingLocalRepositories === []) {
    $report[] = '- none';
} else {
    foreach ($missingLocalRepositories as $name => $path) {
        $report[] = '- ' . $name . ' => ./' . $path;
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
$log[] = 'Composer local package repair report written to: ' . $reportPath;
$log[] = 'MODE ' . ($apply ? 'apply' : 'dry-run');
$log[] = 'MISSING_LOCAL_REPOSITORIES ' . count($missingLocalRepositories);
$log[] = 'COMPOSER_LOCAL_PACKAGE_REPAIR_ERRORS ' . count($errors);
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

/** @return list<string> */
function repositoryPaths(mixed $repositories): array
{
    $paths = [];
    if (! is_array($repositories)) {
        return [];
    }
    foreach ($repositories as $repository) {
        if (is_array($repository) && ($repository['type'] ?? null) === 'path' && isset($repository['url'])) {
            $paths[] = trim((string) $repository['url'], './');
        }
    }
    return array_values(array_unique($paths));
}

/** @param list<string> $paths */
function pathCovered(string $packagePath, array $paths): bool
{
    foreach ($paths as $path) {
        if ($packagePath === $path || str_starts_with($packagePath, rtrim($path, '/') . '/')) {
            return true;
        }
    }
    return false;
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
