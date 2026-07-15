<?php

declare(strict_types=1);

/**
 * Zoosper CMS - Schema Engine Diagnostic Dumper
 * =============================================
 * Phase 1.29 planning.
 *
 * Collects the full source of both schema engines, the migration contract and
 * runner, the CLI schema entrypoints, sample module `config/db_schema.php`
 * declarations, and the central `database/` layout - so the schema-engine
 * unification (and module-owned schema discovery) can be designed with no
 * guessing.
 *
 * Usage:
 *   php bin/dump-schema-engine.php
 *   php bin/dump-schema-engine.php --root=/path --out=dump.txt
 *
 * PCI note: reads source only; never reads .env or secrets.
 */

const MAX_BYTES = 256 * 1024;
const SKIP_DIRS = ['vendor', 'node_modules', '.git', 'storage', 'var'];

/** Basename substrings that mark a schema/migration source file. */
const NAME_NEEDLES = [
    'Schema', 'Migrat', 'Migration', 'DeclarativeSchema', 'SchemaLoader',
    'SchemaMigrator', 'SchemaSqlBuilder', 'SchemaSnapshot', 'SchemaApplier',
    'ColumnDefinition', 'TableDefinition', 'MigrationInterface',
    'MigrationRunner', 'Migrator', 'MigrationRepository',
];

/** Exact target files to dump if present. */
const EXACT_TARGETS = [
    'config/database.php',
    'config/database_policy.php',
];

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

function nameMatches(string $basename): bool
{
    foreach (NAME_NEEDLES as $needle) {
        if (str_contains($basename, $needle)) {
            return true;
        }
    }
    return false;
}

/** @param array<int,string> $argv */
function main(array $argv): int
{
    $args = parseArgs($argv);
    $default = dirname(__DIR__);
    $root = $args['root'] ?? (is_dir($default) ? $default : getcwd());
    $root = rtrim((string) (realpath($root) ?: $root), '/\\');
    $out  = $args['out'] ?? 'zoosper-schema-engine-dump.txt';

    $rel = static fn (string $p): string => ltrim(str_replace($root, '', $p), '/\\');

    $toDump = [];   // rel => absolute
    $missing = [];

    // Strategy 1/2/6: schema & migration source under app/.
    $count = 0;
    foreach (recurse($root . '/app', $root) as $path) {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') {
            continue;
        }
        if (nameMatches(basename($path, '.php')) && $count < 24) {
            $toDump[$rel($path)] = $path;
            $count++;
        }
    }

    // Strategy 3: exact config targets.
    foreach (EXACT_TARGETS as $t) {
        $full = $root . DIRECTORY_SEPARATOR . $t;
        if (is_file($full)) {
            $toDump[$t] = $full;
        } else {
            $missing[] = $t;
        }
    }

    // Strategy 4: CLI schema entrypoints under bin/.
    foreach (recurse($root . '/bin', $root) as $path) {
        $b = strtolower(basename($path));
        if (str_contains($b, 'schema')) {
            $toDump[$rel($path)] = $path;
        }
    }

    // Strategy 5a: sample module db_schema.php (up to 4).
    $schemaCount = 0;
    $dbSchemaPaths = [];
    foreach (recurse($root . '/app', $root) as $path) {
        if (basename($path) === 'db_schema.php' && str_contains($path, DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR)) {
            $dbSchemaPaths[] = $rel($path);
            if ($schemaCount < 4) {
                $toDump[$rel($path)] = $path;
                $schemaCount++;
            }
        }
    }

    // Strategy 5b: database/schema/*.php (up to 4).
    $schemaDirCount = 0;
    foreach (recurse($root . '/database/schema', $root) as $path) {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'php' && $schemaDirCount < 4) {
            $toDump[$rel($path)] = $path;
            $schemaDirCount++;
        }
    }

    ksort($toDump);

    // Discovery (paths only): everything under database/.
    $databaseFiles = [];
    foreach (recurse($root . '/database', $root) as $path) {
        $databaseFiles[] = $rel($path);
    }
    sort($databaseFiles);

    // Root tests/ inspection.
    $testsDir = $root . DIRECTORY_SEPARATOR . 'tests';
    $testsExists = is_dir($testsDir);
    $testsContents = [];
    if ($testsExists) {
        foreach (recurse($testsDir, $root) as $path) {
            $testsContents[] = $rel($path);
        }
        sort($testsContents);
    }

    // Build output.
    $buf  = "ZOOSPER CMS - SCHEMA ENGINE DUMP (Phase 1.29)\n";
    $buf .= str_repeat('=', 60) . "\n";
    $buf .= 'Generated : ' . date('c') . "\n";
    $buf .= 'Repo root : ' . $root . "\n";
    $buf .= 'Dumped    : ' . count($toDump) . " file(s)\n";
    $buf .= "PCI note  : source only; .env not read.\n";
    $buf .= str_repeat('=', 60) . "\n";

    if ($missing !== []) {
        $buf .= "\nMISSING EXACT TARGETS\n" . str_repeat('-', 60) . "\n";
        foreach ($missing as $m) {
            $buf .= '  ! ' . $m . "\n";
        }
    }

    $buf .= "\nALL MODULE config/db_schema.php FILES\n" . str_repeat('-', 60) . "\n";
    $buf .= $dbSchemaPaths === [] ? "  (none found)\n" : implode("\n", array_map(static fn ($p) => '  - ' . $p, $dbSchemaPaths)) . "\n";

    $buf .= "\nDATABASE/ DIRECTORY (paths only)\n" . str_repeat('-', 60) . "\n";
    $buf .= $databaseFiles === [] ? "  (none found)\n" : implode("\n", array_map(static fn ($p) => '  - ' . $p, $databaseFiles)) . "\n";

    $buf .= "\nROOT tests/ DIRECTORY\n" . str_repeat('-', 60) . "\n";
    if (!$testsExists) {
        $buf .= "  (does not exist)\n";
    } elseif ($testsContents === []) {
        $buf .= "  EXISTS and is EMPTY -> safe to remove (rmdir tests).\n";
    } else {
        $buf .= "  EXISTS with contents:\n";
        foreach ($testsContents as $t) {
            $buf .= '    - ' . $t . "\n";
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

    fwrite(STDOUT, "Schema engine dump written to: {$out}\n");
    fwrite(STDOUT, '  Files dumped        : ' . $dumped . "\n");
    fwrite(STDOUT, '  database/ files     : ' . count($databaseFiles) . "\n");
    fwrite(STDOUT, '  module db_schema.php: ' . count($dbSchemaPaths) . "\n");
    fwrite(STDOUT, '  root tests/         : ' . ($testsExists ? ($testsContents === [] ? 'EMPTY (removable)' : count($testsContents) . ' file(s)') : 'absent') . "\n");
    if ($missing !== []) {
        fwrite(STDOUT, '  Missing targets     : ' . count($missing) . "\n");
    }

    return 0;
}

exit(main($argv));
