<?php

declare(strict_types=1);

/**
 * Zoosper CMS - Tools Inventory / Migration Tracker
 * =================================================
 * Phase 1.22 - Foundation Consolidation.
 *
 * Classifies every script under tools/ and tests/ into four buckets so we can
 * measure legacy-tooling retirement progress over time (e.g. how many verify-*
 * scripts remain vs. have been migrated to Pest).
 *
 * Buckets:
 *   DELETE_NOW       one-shot artifacts safe to remove now
 *   MIGRATE_TO_PEST  the old crude verify-* test net (retire per policy)
 *   KEEP_OPS         live operational/diagnostic tools
 *   REVIEW           anything unrecognised (classify manually - never silent)
 *
 * Usage:
 *   php bin/tools-inventory.php
 *   php bin/tools-inventory.php --root=/path/to/zoosper --out=var/reports/tools-inventory.txt
 *
 * PCI note: reads filenames/paths only; never reads .env or file contents.
 */

/** @param array<int,string> $argv @return array<string,string> */
function parseArgs(array $argv): array
{
    $out = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (preg_match('/^--([a-z0-9_-]+)=(.*)$/i', $arg, $m)) {
            $out[$m[1]] = $m[2];
        }
    }
    return $out;
}

/**
 * Classify a basename into a bucket.
 */
function classify(string $name, string $relPath): string
{
    if ($relPath === 'tests/run.php') {
        return 'DELETE_NOW';
    }

    $deleteNow = ['apply-', 'add-', 'export-phase-', 'phase015-'];
    foreach ($deleteNow as $prefix) {
        if (str_starts_with($name, $prefix)) {
            return 'DELETE_NOW';
        }
    }

    if (str_starts_with($name, 'verify-') || $name === 'run-verification-suite.php') {
        return 'MIGRATE_TO_PEST';
    }

    $opsPrefixes = [
        'diagnose-', 'audit-', 'repair-', 'quarantine-', 'clean-', 'demo-',
        'inspect-', 'send-', 'reset-', 'publish-', 'migrate-', 'assert-',
        'bootstrap', 'wire-', 'fix-',
    ];
    foreach ($opsPrefixes as $prefix) {
        if (str_starts_with($name, $prefix)) {
            return 'KEEP_OPS';
        }
    }

    $opsExact = [
        'page-content-schema-db.php',
        'public-webroot-policy.php',
        'remove-public-themes-directory.php',
    ];
    if (in_array($name, $opsExact, true) || str_contains($name, 'mailpit-docker')) {
        return 'KEEP_OPS';
    }

    return 'REVIEW';
}

/**
 * Recursively collect .php/.sh script paths under a directory.
 *
 * Renamed from scanDir() to avoid a case-insensitive clash with PHP's built-in
 * scandir().
 *
 * @return array<int,string>
 */
function collectScripts(string $dir): array
{
    if (!is_dir($dir)) {
        return [];
    }
    $found = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $info) {
        /** @var SplFileInfo $info */
        if ($info->isFile()) {
            $ext = strtolower($info->getExtension());
            if ($ext === 'php' || $ext === 'sh') {
                $found[] = $info->getPathname();
            }
        }
    }
    return $found;
}

/** @param array<int,string> $argv */
function main(array $argv): int
{
    $args = parseArgs($argv);
    $default = dirname(__DIR__);
    $root = $args['root'] ?? (is_dir($default) ? $default : getcwd());
    $root = rtrim((string) (realpath($root) ?: $root), '/\\');
    $out  = $args['out'] ?? 'var/reports/tools-inventory.txt';

    $rel = static fn (string $p): string => ltrim(str_replace($root, '', $p), '/\\');

    $buckets = [
        'DELETE_NOW'      => [],
        'MIGRATE_TO_PEST' => [],
        'KEEP_OPS'        => [],
        'REVIEW'          => [],
    ];

    foreach (['tools', 'tests'] as $sub) {
        foreach (collectScripts($root . DIRECTORY_SEPARATOR . $sub) as $path) {
            $r = $rel($path);
            $buckets[classify(basename($path), $r)][] = $r;
        }
    }
    foreach ($buckets as &$list) {
        sort($list);
    }
    unset($list);

    // Build report.
    $buf  = "ZOOSPER CMS - TOOLS INVENTORY (Phase 1.22)\n";
    $buf .= str_repeat('=', 60) . "\n";
    $buf .= 'Generated : ' . date('c') . "\n";
    $buf .= 'Repo root : ' . $root . "\n";
    foreach ($buckets as $name => $list) {
        $buf .= sprintf('%-16s : %d file(s)' . "\n", $name, count($list));
    }
    $buf .= "PCI note  : filenames only; .env and file contents not read.\n";
    $buf .= str_repeat('=', 60) . "\n";

    foreach ($buckets as $name => $list) {
        $buf .= "\n[" . $name . "]  (" . count($list) . ")\n";
        $buf .= str_repeat('-', 60) . "\n";
        if ($list === []) {
            $buf .= "  (none)\n";
            continue;
        }
        foreach ($list as $r) {
            $buf .= '  - ' . $r . "\n";
        }
    }

    $outPath = $root . DIRECTORY_SEPARATOR . $out;
    if (!is_dir(dirname($outPath))) {
        @mkdir(dirname($outPath), 0775, true);
    }
    file_put_contents($outPath, $buf);

    fwrite(STDOUT, "Tools inventory written to: {$out}\n");
    foreach ($buckets as $name => $list) {
        fwrite(STDOUT, sprintf('  %-16s %d' . "\n", $name, count($list)));
    }
    if ($buckets['REVIEW'] !== []) {
        fwrite(STDOUT, "\n  NOTE: REVIEW bucket is non-empty - classify these manually.\n");
    }

    return 0;
}

exit(main($argv));
