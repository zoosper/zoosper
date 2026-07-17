<?php

declare(strict_types=1);

/**
 * Zoosper CMS - Site-Resolution Diagnostic Dumper (Phase 1.34 planning).
 *
 * Collects the files needed to unify the two parallel site-resolution systems
 * with zero guessing:
 *   - SiteRepository / Site model (DB-backed, admin-managed source of truth)
 *   - SiteContext / SiteContextResolver / SiteContextResolverFactory
 *   - CurrentSiteContext (future immutable per-request resolution/cache layer)
 *   - config/sites.php (source-of-truth role to be reviewed)
 *   - CdnUrlResolver, CacheKeyBuilder, TemplateViewContextProvider consumers
 *
 * Usage:
 *   php bin/dump-site-resolution.php
 *   php bin/dump-site-resolution.php --root=. --out=zoosper-site-resolution-dump.txt
 *
 * PCI note: reads source only; never reads .env or secrets.
 */

const MAX_BYTES = 262144;
const SKIP_DIRS = ['vendor', 'node_modules', '.git', 'storage', 'var'];

const TARGETS = [
    'config/sites.php',
    'config/cdn.php',
    'app/zoosper-core/config/services.php',
    'app/zoosper-site/config/services.php',
    'app/zoosper-site/config/db_schema.php',
];

const NAME_NEEDLES = [
    'Site',
    'CurrentSite',
    'SiteContext',
    'SiteResolver',
    'SiteRepository',
    'CdnUrlResolver',
    'CacheKeyBuilder',
    'TemplateViewContextProvider',
];

/** @param array<int, string> $argv @return array<string, string> */
function parseArgs(array $argv): array
{
    $options = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (preg_match('/^--([a-z0-9_-]+)=(.*)$/i', $arg, $matches) === 1) {
            $options[$matches[1]] = $matches[2];
        }
    }
    return $options;
}

function isSkipped(string $relative): bool
{
    $relative = str_replace('\\', '/', $relative);
    foreach (SKIP_DIRS as $directory) {
        if (str_starts_with($relative, $directory . '/') || str_contains($relative, '/' . $directory . '/')) {
            return true;
        }
    }
    return false;
}

/** @return list<string> */
function recursePhp(string $directory, string $root): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $found = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $info) {
        /** @var SplFileInfo $info */
        if (!$info->isFile() || strtolower($info->getExtension()) !== 'php') {
            continue;
        }

        $relative = ltrim(str_replace($root, '', $info->getPathname()), '/\\');
        if (!isSkipped($relative)) {
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

/** @param array<int, string> $argv */
function main(array $argv): int
{
    $args = parseArgs($argv);
    $defaultRoot = dirname(__DIR__);
    $root = $args['root'] ?? (is_dir($defaultRoot) ? $defaultRoot : (string) getcwd());
    $root = rtrim((string) (realpath($root) ?: $root), '/\\');
    $out = $args['out'] ?? 'zoosper-site-resolution-dump.txt';

    $relativePath = static fn (string $path): string => ltrim(str_replace($root, '', $path), '/\\');

    /** @var array<string, string> $toDump */
    $toDump = [];
    $missing = [];

    foreach (TARGETS as $target) {
        $full = $root . '/' . $target;
        if (is_file($full)) {
            $toDump[$target] = $full;
        } else {
            $missing[] = $target;
        }
    }

    $matched = 0;
    foreach (recursePhp($root . '/app', $root) as $path) {
        if ($matched >= 20) {
            break;
        }

        if (nameMatches(basename($path, '.php'))) {
            $relative = $relativePath($path);
            if (!isset($toDump[$relative])) {
                $toDump[$relative] = $path;
                $matched++;
            }
        }
    }

    $callerReferences = [];
    foreach (recursePhp($root . '/app', $root) as $path) {
        $size = @filesize($path);
        if ($size === false || $size > MAX_BYTES) {
            continue;
        }

        $contents = (string) @file_get_contents($path);
        if (
            str_contains($contents, 'CurrentSiteContext')
            || str_contains($contents, 'SiteContextResolver')
            || str_contains($contents, 'dynamicForContext')
        ) {
            $callerReferences[] = $relativePath($path);
        }
    }

    sort($callerReferences);
    $callerReferences = array_values(array_unique($callerReferences));
    ksort($toDump);

    $buffer = "ZOOSPER CMS - SITE RESOLUTION DUMP (Phase 1.34)\n";
    $buffer .= str_repeat('=', 60) . "\n";
    $buffer .= 'Generated : ' . date('c') . "\n";
    $buffer .= 'Repo root : ' . $root . "\n";
    $buffer .= 'Dumped    : ' . count($toDump) . " file(s)\n";
    $buffer .= "PCI note  : source only; .env not read.\n";
    $buffer .= str_repeat('=', 60) . "\n";

    if ($missing !== []) {
        $buffer .= "\nMISSING EXACT TARGETS (renamed or absent)\n" . str_repeat('-', 60) . "\n";
        foreach ($missing as $item) {
            $buffer .= '  ! ' . $item . "\n";
        }
    }

    $buffer .= "\nFILES REFERENCING SITE CONTEXT / RESOLVER / dynamicForContext (paths only)\n" . str_repeat('-', 60) . "\n";
    $buffer .= $callerReferences === []
        ? "  (none found)\n"
        : implode("\n", array_map(static fn (string $path): string => '  - ' . $path, $callerReferences)) . "\n";

    $buffer .= "\n" . str_repeat('=', 60) . "\nFILE CONTENTS\n" . str_repeat('=', 60) . "\n";

    $dumped = 0;
    foreach ($toDump as $relative => $full) {
        $size = (int) filesize($full);
        if ($size > MAX_BYTES) {
            $buffer .= "\n\n==== FILE: {$relative} (SKIPPED {$size} bytes > limit) ====\n";
            continue;
        }

        $contents = (string) file_get_contents($full);
        $buffer .= "\n\n==== FILE: {$relative} ({$size} bytes) ====\n";
        $buffer .= $contents;
        if (!str_ends_with($contents, "\n")) {
            $buffer .= "\n";
        }
        $buffer .= "==== END FILE: {$relative} ====\n";
        $dumped++;
    }

    file_put_contents($out, $buffer);

    fwrite(STDOUT, "Site resolution dump written to: {$out}\n");
    fwrite(STDOUT, '  Files dumped        : ' . $dumped . "\n");
    fwrite(STDOUT, '  Caller references   : ' . count($callerReferences) . "\n");
    fwrite(STDOUT, '  Missing targets     : ' . count($missing) . "\n");

    return 0;
}

exit(main($argv));
