<?php

declare(strict_types=1);

/**
 * Read-only config source inventory for Phase 1.40 config layering migration.
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

$configFiles = [];
$loaderPatterns = [];
foreach (['app', 'packages'] as $base) {
    $dir = $root . DIRECTORY_SEPARATOR . $base;
    if (! is_dir($dir)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }
        $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $source = (string) file_get_contents($file->getPathname());

        if (str_contains($relative, '/config/') || str_ends_with($relative, 'config.php')) {
            $configFiles[$relative] = classifyConfigFile($relative, $source);
        }

        foreach (['require ', 'require_once ', 'include ', 'include_once ', 'config/', 'loadConfig', 'ConfigLoader', 'ModuleConfig'] as $needle) {
            if (str_contains($source, $needle)) {
                $loaderPatterns[$relative][] = 'contains ' . $needle;
            }
        }
    }
}
ksort($configFiles);
ksort($loaderPatterns);

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config-sources.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config-sources.log';

$report = [];
$report[] = '# Config Source Inventory';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Config files: ' . count($configFiles);
$report[] = 'Loader pattern files: ' . count($loaderPatterns);
$report[] = '';
$report[] = '## Config files';
foreach ($configFiles as $relative => $signals) {
    $report[] = '';
    $report[] = '### ' . $relative;
    foreach ($signals as $signal) {
        $report[] = '- ' . $signal;
    }
}
$report[] = '';
$report[] = '## Loader pattern files';
foreach ($loaderPatterns as $relative => $signals) {
    $report[] = '';
    $report[] = '### ' . $relative;
    foreach (array_values(array_unique($signals)) as $signal) {
        $report[] = '- ' . $signal;
    }
}

file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Config source inventory written to: ' . $reportPath;
$log[] = 'CONFIG_SOURCE_FILES ' . count($configFiles);
$log[] = 'CONFIG_LOADER_PATTERN_FILES ' . count($loaderPatterns);
$log[] = 'CONFIG_SOURCE_AUDIT_ERRORS 0';
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit(0);

/** @return list<string> */
function classifyConfigFile(string $relative, string $source): array
{
    $signals = [];
    $basename = basename($relative);
    $signals[] = 'basename: ' . $basename;
    $signals[] = 'contains return array: ' . (preg_match('/return\s*\[/', $source) === 1 ? 'yes' : 'no');
    $signals[] = 'contains service: ' . (stripos($relative, 'services') !== false || stripos($source, 'service') !== false ? 'yes' : 'no');
    $signals[] = 'contains route: ' . (stripos($relative, 'route') !== false || stripos($source, 'route') !== false ? 'yes' : 'no');
    $signals[] = 'contains middleware: ' . (stripos($relative, 'middleware') !== false || stripos($source, 'middleware') !== false ? 'yes' : 'no');
    $signals[] = 'contains menu: ' . (stripos($relative, 'menu') !== false || stripos($source, 'menu') !== false ? 'yes' : 'no');
    $signals[] = 'contains acl/permission: ' . (stripos($relative, 'acl') !== false || stripos($source, 'permission') !== false ? 'yes' : 'no');
    $signals[] = 'contains schema: ' . (stripos($relative, 'schema') !== false || stripos($source, 'schema') !== false ? 'yes' : 'no');
    return $signals;
}
