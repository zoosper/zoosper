<?php

declare(strict_types=1);

/**
 * Zoosper tools inventory report generator.
 *
 * This command is intentionally filename-only. It does not read tool contents,
 * .env files, or project secrets.
 */

$root = dirname(__DIR__);
$toolsDir = $root . DIRECTORY_SEPARATOR . 'tools';
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($toolsDir)) {
    fwrite(STDERR, "Tools directory not found: {$toolsDir}" . PHP_EOL);
    exit(1);
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, "Unable to create report directory: {$outputDir}" . PHP_EOL);
    exit(1);
}

$files = glob($toolsDir . DIRECTORY_SEPARATOR . '*') ?: [];
$relativeFiles = [];

foreach ($files as $file) {
    if (! is_file($file)) {
        continue;
    }

    $relativeFiles[] = 'tools/' . basename($file);
}

sort($relativeFiles);

$categories = [
    'DELETE_NOW' => [],
    'MIGRATE_TO_PEST' => [],
    'KEEP_OPS' => [],
    'REVIEW' => [],
];

foreach ($relativeFiles as $file) {
    $categories[classifyTool($file)][] = $file;
}

$generatedAt = (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'tools-inventory.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'tools-inventory.log';

$report = [];
$report[] = '## ZOOSPER CMS - TOOLS INVENTORY';
$report[] = '';
$report[] = '## Generated : ' . $generatedAt;
$report[] = 'Repo root : ' . $root;
$report[] = sprintf('DELETE_NOW       : %d file(s)', count($categories['DELETE_NOW']));
$report[] = sprintf('MIGRATE_TO_PEST  : %d file(s)', count($categories['MIGRATE_TO_PEST']));
$report[] = sprintf('KEEP_OPS         : %d file(s)', count($categories['KEEP_OPS']));
$report[] = sprintf('REVIEW           : %d file(s)', count($categories['REVIEW']));
$report[] = 'PCI note  : filenames only; .env and file contents not read.';

foreach ($categories as $category => $items) {
    $report[] = '';
    $report[] = '### [' . $category . ']  (' . count($items) . ')';
    $report[] = '';

    if ($items === []) {
        $report[] = '(none)';
        continue;
    }

    foreach ($items as $item) {
        $report[] = '- ' . $item;
    }
}

$reportText = implode(PHP_EOL, $report) . PHP_EOL;

file_put_contents($txtPath, $reportText);

$log = [];
$log[] = 'Tools inventory written to: ' . $txtPath;
$log[] = sprintf('DELETE_NOW       %d', count($categories['DELETE_NOW']));
$log[] = sprintf('MIGRATE_TO_PEST  %d', count($categories['MIGRATE_TO_PEST']));
$log[] = sprintf('KEEP_OPS         %d', count($categories['KEEP_OPS']));
$log[] = sprintf('REVIEW           %d', count($categories['REVIEW']));

file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

function classifyTool(string $relativePath): string
{
    $name = basename($relativePath);

    if (str_starts_with($name, 'verify-') && str_ends_with($name, '.php')) {
        return 'MIGRATE_TO_PEST';
    }

    foreach (operationalPrefixes() as $prefix) {
        if (str_starts_with($name, $prefix)) {
            return 'KEEP_OPS';
        }
    }

    // Conservative default: an unknown tool is still treated as operational
    // rather than deletion-ready. REVIEW is reserved for deliberate future use.
    return 'KEEP_OPS';
}

/**
 * @return list<string>
 */
function operationalPrefixes(): array
{
    return [
        'assert-',
        'audit-',
        'bootstrap',
        'clean-',
        'demo-',
        'diagnose-',
        'ensure-',
        'fix-',
        'generate-',
        'inspect-',
        'migrate-',
        'normalise-',
        'page-content-',
        'pilot-',
        'public-webroot-',
        'publish-',
        'quarantine-',
        'remove-',
        'repair-',
        'reset-',
        'send-',
        'smoke-',
        'start-',
        'stop-',
        'sync-',
        'wire-',
    ];
}
