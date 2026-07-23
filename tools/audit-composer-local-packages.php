<?php

declare(strict_types=1);

/**
 * Read-only audit for local Composer package visibility.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$rootComposer = $root . DIRECTORY_SEPARATOR . 'composer.json';
$errors = [];
if (! is_file($rootComposer)) {
    $errors[] = 'Root composer.json was not found.';
}

$rootData = is_file($rootComposer) ? json_decode((string) file_get_contents($rootComposer), true) : null;
if (! is_array($rootData)) {
    $errors[] = 'Root composer.json could not be decoded.';
    $rootData = [];
}

$packages = discoverLocalComposerPackages($root);
$repositories = $rootData['repositories'] ?? [];
$repoPaths = repositoryPaths($repositories);
$visiblePackages = visiblePackageNames($packages, $repoPaths);
$requiredNames = requiredPackageNames($rootData, $packages);

$missingRequired = [];
foreach ($requiredNames as $name) {
    if (str_starts_with($name, 'zoosper/') && ! isset($visiblePackages[$name]) && ! (($rootData['name'] ?? null) === $name)) {
        $missingRequired[] = $name;
    }
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer-local-packages.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'composer-local-packages.log';

$report = [];
$report[] = '# Composer Local Package Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Root package: ' . (string) ($rootData['name'] ?? 'unknown');
$report[] = 'Local packages found: ' . count($packages);
$report[] = 'Repository paths found: ' . count($repoPaths);
$report[] = 'Missing required local zoosper packages: ' . count($missingRequired);
$report[] = 'Errors: ' . count($errors);
$report[] = '';
$report[] = '## Local packages';
foreach ($packages as $path => $data) {
    $report[] = '- ' . ($data['name'] ?? 'unnamed') . ' => ' . $path;
}
$report[] = '';
$report[] = '## Root repository paths';
foreach ($repoPaths as $path) {
    $report[] = '- ' . $path;
}
$report[] = '';
$report[] = '## Missing required local zoosper packages';
if ($missingRequired === []) {
    $report[] = '- none';
} else {
    foreach ($missingRequired as $name) {
        $report[] = '- ' . $name;
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
$log[] = 'Composer local package audit written to: ' . $reportPath;
$log[] = 'LOCAL_PACKAGES ' . count($packages);
$log[] = 'REPOSITORY_PATHS ' . count($repoPaths);
$log[] = 'MISSING_LOCAL_ZOOSPER_REQUIREMENTS ' . count($missingRequired);
$log[] = 'COMPOSER_LOCAL_PACKAGE_ERRORS ' . count($errors);
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

/** @param array<string,array<string,mixed>> $packages @param list<string> $repoPaths @return array<string,string> */
function visiblePackageNames(array $packages, array $repoPaths): array
{
    $visible = [];
    foreach ($packages as $path => $data) {
        foreach ($repoPaths as $repoPath) {
            if ($path === $repoPath || str_starts_with($path, rtrim($repoPath, '/') . '/')) {
                $visible[(string) $data['name']] = $path;
            }
        }
    }
    return $visible;
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
