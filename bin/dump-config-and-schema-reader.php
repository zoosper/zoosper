<?php

declare(strict_types=1);

/**
 * Zoosper CMS - Config Layering & Schema-Reader Diagnostic Dumper
 * ==============================================================
 * Unblocks two upcoming pieces of work with zero guessing:
 *   (1) Folding database/schema/*.php column-adds into their owning modules
 *       (needs the code that READS database/schema/*.php).
 *   (3) Phase 1.30 config/ layering (needs ConfigRepository + any config
 *       loader/aggregator/merger, and the current config layout).
 *
 * Usage:
 *   php bin/dump-config-and-schema-reader.php
 *   php bin/dump-config-and-schema-reader.php --root=/path --out=dump.txt
 *
 * PCI note: reads source only; never reads .env or secrets.
 */

const MAX_BYTES = 256 * 1024;
const SKIP_DIRS = ['vendor', 'node_modules', '.git', 'storage', 'var'];

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

function isSkipped(string $rel): bool
{
    $rel = str_replace('\\', '/', $rel);
    foreach (SKIP_DIRS as $d) {
        if (str_starts_with($rel, $d . '/') || str_contains($rel, '/' . $d . '/')) {
            return true;
        }
    }
    return false;
}

/** @return array<int,string> */
function recurse(string $dir, string $root): array
{
    if (!is_dir($dir)) {
        return [];
    }
    $found = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $info) {
        /** @var SplFileInfo $info */
        if (!$info->isFile()) {
            continue;
        }
        $rel = ltrim(str_replace($root, '', $info->getPathname()), '/\\');
        if (!isSkipped($rel)) {
            $found[] = $info->getPathname();
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
    $out  = $args['out'] ?? 'zoosper-config-schema-dump.txt';

    $rel = static fn (string $p): string => ltrim(str_replace($root, '', $p), '/\\');

    $toDump = [];   // rel => absolute

    $allApp = recurse($root . '/app', $root);

    // --- A) Config layering targets ---
    // ConfigRepository + config loader/repository/aggregator/merger classes.
    $cfgCount = 0;
    foreach ($allApp as $path) {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') {
            continue;
        }
        $b = basename($path, '.php');
        $isRepo = ($b === 'ConfigRepository');
        $isLoaderish = str_contains($b, 'Config')
            && (str_contains($b, 'Loader') || str_contains($b, 'Repository') || str_contains($b, 'Aggregator') || str_contains($b, 'Merger'));
        if (($isRepo || $isLoaderish) && $cfgCount < 8) {
            if (!isset($toDump[$rel($path)])) {
                $toDump[$rel($path)] = $path;
                $cfgCount++;
            }
        }
    }

    // --- B) database/schema reader (content contains 'database/schema') ---
    $schemaRefFiles = [];   // rel paths that reference database/schema
    $schemaReaderDumped = 0;
    foreach (array_merge($allApp, recurse($root . '/database', $root), recurse($root . '/bin', $root), recurse($root . '/tools', $root)) as $path) {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, ['php'], true)) {
            continue;
        }
        // skip the schema files themselves
        $r = $rel($path);
        if (str_starts_with($r, 'database/schema/')) {
            continue;
        }
        $size = @filesize($path);
        if ($size === false || $size > MAX_BYTES) {
            continue;
        }
        $contents = (string) @file_get_contents($path);
        if (str_contains($contents, 'database/schema')) {
            $schemaRefFiles[] = $r;
            if ($schemaReaderDumped < 6 && !isset($toDump[$r])) {
                $toDump[$r] = $path;
                $schemaReaderDumped++;
            }
        }
    }

    // Installer / column-applier classes by name.
    $instCount = 0;
    foreach ($allApp as $path) {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') {
            continue;
        }
        $b = basename($path, '.php');
        if ((str_contains($b, 'ColumnApplier') || str_contains($b, 'SchemaColumn') || str_contains($b, 'Installer') || str_contains($b, 'Install')) && $instCount < 6) {
            if (!isset($toDump[$rel($path)])) {
                $toDump[$rel($path)] = $path;
                $instCount++;
            }
        }
    }

    // The 3 database/schema files (+ sql) for convenience.
    foreach ([
        'database/schema/admin_user_locale.php',
        'database/schema/page_seo_metadata.php',
        'database/schema/page_content_format.php',
        'database/schema/entity_extension_values.sql',
    ] as $t) {
        $full = $root . DIRECTORY_SEPARATOR . $t;
        if (is_file($full)) {
            $toDump[$t] = $full;
        }
    }

    ksort($toDump);

    // --- Discovery: config layout ---
    $rootConfigFiles = [];
    foreach (glob($root . '/config/*.php') ?: [] as $p) {
        $rootConfigFiles[] = $rel($p);
    }
    sort($rootConfigFiles);

    // Per-module config files.
    $moduleConfig = [];  // module => list of config filenames
    foreach ($allApp as $path) {
        $r = str_replace('\\', '/', $rel($path));
        if (preg_match('#^app/([^/]+)/config/([^/]+)$#', $r, $m)) {
            $moduleConfig[$m[1]][] = $m[2];
        }
    }
    ksort($moduleConfig);

    // Installer module listing.
    $installDir = $root . '/app/zoosper-install';
    $installFiles = [];
    if (is_dir($installDir)) {
        foreach (recurse($installDir, $root) as $p) {
            $installFiles[] = $rel($p);
        }
        sort($installFiles);
    }

    sort($schemaRefFiles);
    $schemaRefFiles = array_values(array_unique($schemaRefFiles));

    // --- Build output ---
    $buf  = "ZOOSPER CMS - CONFIG LAYERING & SCHEMA-READER DUMP\n";
    $buf .= str_repeat('=', 60) . "\n";
    $buf .= 'Generated : ' . date('c') . "\n";
    $buf .= 'Repo root : ' . $root . "\n";
    $buf .= 'Dumped    : ' . count($toDump) . " file(s)\n";
    $buf .= "PCI note  : source only; .env not read.\n";
    $buf .= str_repeat('=', 60) . "\n";

    $buf .= "\nROOT config/ LAYOUT (paths only)\n" . str_repeat('-', 60) . "\n";
    $buf .= $rootConfigFiles === [] ? "  (none)\n" : implode("\n", array_map(static fn ($p) => '  - ' . $p, $rootConfigFiles)) . "\n";

    $buf .= "\nPER-MODULE config/ FILES\n" . str_repeat('-', 60) . "\n";
    if ($moduleConfig === []) {
        $buf .= "  (none)\n";
    } else {
        foreach ($moduleConfig as $mod => $filesList) {
            sort($filesList);
            $buf .= '  [' . $mod . "]\n";
            foreach ($filesList as $f) {
                $buf .= '    - ' . $f . "\n";
            }
        }
    }

    $buf .= "\nFILES REFERENCING 'database/schema' (the reader we need)\n" . str_repeat('-', 60) . "\n";
    if ($schemaRefFiles === []) {
        $buf .= "  (NONE FOUND) -> database/schema/*.php appears UNUSED/legacy.\n";
        $buf .= "  If confirmed unused, those column-adds can be folded into the owning\n";
        $buf .= "  modules' config/db_schema.php and the database/schema/ files deleted.\n";
    } else {
        foreach ($schemaRefFiles as $f) {
            $buf .= '  - ' . $f . "\n";
        }
    }

    if ($installFiles !== []) {
        $buf .= "\napp/zoosper-install/ CONTENTS (paths only)\n" . str_repeat('-', 60) . "\n";
        foreach ($installFiles as $f) {
            $buf .= '  - ' . $f . "\n";
        }
    }

    $buf .= "\n" . str_repeat('=', 60) . "\nFILE CONTENTS\n" . str_repeat('=', 60) . "\n";
    $dumped = 0;
    foreach ($toDump as $r => $full) {
        $size = (int) filesize($full);
        if ($size > MAX_BYTES) {
            $buf .= "\n\n==== FILE: {$r} (SKIPPED {$size} bytes > limit) ====\n";
            continue;
        }
        $contents = (string) file_get_contents($full);
        $buf .= "\n\n==== FILE: {$r} ({$size} bytes) ====\n";
        $buf .= $contents;
        if (!str_ends_with($contents, "\n")) {
            $buf .= "\n";
        }
        $buf .= "==== END FILE: {$r} ====\n";
        $dumped++;
    }

    file_put_contents($out, $buf);

    fwrite(STDOUT, "Config + schema-reader dump written to: {$out}\n");
    fwrite(STDOUT, '  Files dumped            : ' . $dumped . "\n");
    fwrite(STDOUT, '  Root config/ files      : ' . count($rootConfigFiles) . "\n");
    fwrite(STDOUT, '  Modules with config     : ' . count($moduleConfig) . "\n");
    fwrite(STDOUT, '  database/schema refs    : ' . count($schemaRefFiles) . "\n");
    if ($schemaRefFiles === []) {
        fwrite(STDOUT, "  NOTE: nothing references database/schema -> likely legacy/unused (safe to fold).\n");
    }

    return 0;
}

exit(main($argv));
