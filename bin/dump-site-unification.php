<?php

declare(strict_types=1);

/**
 * Zoosper CMS - Site Unification & Render-Thread Dumper (Phase 1.34b planning).
 *
 * Phase 1.34b has two coupled goals:
 *   1. Data-model unification: make the DB-backed SiteRepository the single
 *      source of truth; demote config/sites.php to bootstrap defaults; enrich the
 *      sites schema with the rich dimensions System A needs (locale, currency,
 *      base_url, website/store/store-view codes).
 *   2. Finish the request-context thread through the render path, removing the
 *      last $_SERVER reads (CurrentSiteContext factory + TemplateViewContextProvider).
 *
 * This collects, with zero guessing:
 *   - the render path: TemplateRenderer + engines + how TemplateViewContextProvider
 *     is invoked (theme.*_template_renderer wiring)
 *   - the site systems: SiteRepository/Site/SiteResolver (System B) and the rich
 *     SiteContext/SiteContextResolver (System A)
 *   - the sites write paths (admin controller, create/update, migrations, schema)
 *   - config/sites.php + the theme config/services.php
 *
 * Usage:
 *   php bin/dump-site-unification.php
 *   php bin/dump-site-unification.php --root=. --out=zoosper-site-unification-dump.txt
 *
 * PCI note: reads source only; never reads .env or secrets.
 */

const MAX_BYTES = 262144;
const SKIP_DIRS = ['vendor', 'node_modules', '.git', 'storage', 'var'];

/** Exact target files to dump if present. */
const TARGETS = [
    'config/sites.php',
    'app/zoosper-core/src/View/TemplateViewContextProvider.php',
    'app/zoosper-core/src/Site/SiteContext.php',
    'app/zoosper-core/src/Site/SiteContextResolver.php',
    'app/zoosper-core/src/Site/SiteContextResolverFactory.php',
    'app/zoosper-core/src/Site/CurrentSiteContext.php',
    'app/zoosper-theme/config/services.php',
    'app/zoosper-site/config/services.php',
    'app/zoosper-site/config/db_schema.php',
    'app/zoosper-site/config/admin_routes.php',
    'app/zoosper-site/config/controllers.php',
    'app/zoosper-page/src/Service/PageRenderer.php',
    'app/zoosper-page/config/controllers.php',
];

/** Basename substrings that mark a render-path or site source file. */
const NAME_NEEDLES = [
    'TemplateRenderer', 'TemplateEngine', 'TemplateView', 'ThemeResolver',
    'Site', 'SiteRepository', 'SiteResolver', 'SiteContext', 'SiteDomain',
    'SiteAdminController', 'PageRenderer',
];

/** @param array<int,string> $argv @return array<string,string> */
function parseArgs(array $argv): array
{
    $out = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (preg_match('/^--([a-z0-9_-]+)=(.*)$/i', $arg, $m) === 1) {
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

/** @return list<string> */
function recursePhp(string $dir, string $root): array
{
    if (!is_dir($dir)) {
        return [];
    }
    $found = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $info) {
        /** @var SplFileInfo $info */
        if (!$info->isFile() || strtolower($info->getExtension()) !== 'php') {
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
    $root = $args['root'] ?? (is_dir($default) ? $default : (string) getcwd());
    $root = rtrim((string) (realpath($root) ?: $root), '/\\');
    $out = $args['out'] ?? 'zoosper-site-unification-dump.txt';

    $rel = static fn (string $p): string => ltrim(str_replace($root, '', $p), '/\\');

    /** @var array<string,string> $toDump */
    $toDump = [];
    $missing = [];

    foreach (TARGETS as $t) {
        $full = $root . '/' . $t;
        if (is_file($full)) {
            $toDump[$t] = $full;
        } else {
            $missing[] = $t;
        }
    }

    // Name-matched render/site classes under app/ (dump up to 24).
    $count = 0;
    foreach (recursePhp($root . '/app', $root) as $path) {
        if ($count >= 24) {
            break;
        }
        if (nameMatches(basename($path, '.php'))) {
            $r = $rel($path);
            if (!isset($toDump[$r])) {
                $toDump[$r] = $path;
                $count++;
            }
        }
    }

    // The site tables migrations (create + any alter) - dump all under database/migrations
    // whose name mentions site.
    foreach (recursePhp($root . '/database/migrations', $root) as $path) {
        if (str_contains(strtolower(basename($path)), 'site')) {
            $toDump[$rel($path)] = $path;
        }
    }

    // Discovery (paths only): everything that calls TemplateViewContextProvider,
    // ->data() calls, or reads $_SERVER['HTTP_HOST'] - the surface we must thread/clean.
    $threadRefs = [];
    $serverHostRefs = [];
    foreach (recursePhp($root . '/app', $root) as $path) {
        $size = @filesize($path);
        if ($size === false || $size > MAX_BYTES) {
            continue;
        }
        $contents = (string) @file_get_contents($path);
        if (str_contains($contents, 'TemplateViewContextProvider') || str_contains($contents, 'TemplateRenderer')) {
            $threadRefs[] = $rel($path);
        }
        if (str_contains($contents, "\$_SERVER['HTTP_HOST']") || str_contains($contents, '$_SERVER["HTTP_HOST"]')) {
            $serverHostRefs[] = $rel($path);
        }
    }
    sort($threadRefs);
    sort($serverHostRefs);
    $threadRefs = array_values(array_unique($threadRefs));
    $serverHostRefs = array_values(array_unique($serverHostRefs));

    ksort($toDump);

    $buf  = "ZOOSPER CMS - SITE UNIFICATION & RENDER-THREAD DUMP (Phase 1.34b)\n";
    $buf .= str_repeat('=', 60) . "\n";
    $buf .= 'Generated : ' . date('c') . "\n";
    $buf .= 'Repo root : ' . $root . "\n";
    $buf .= 'Dumped    : ' . count($toDump) . " file(s)\n";
    $buf .= "PCI note  : source only; .env not read.\n";
    $buf .= str_repeat('=', 60) . "\n";

    if ($missing !== []) {
        $buf .= "\nMISSING EXACT TARGETS (renamed or absent)\n" . str_repeat('-', 60) . "\n";
        foreach ($missing as $m) {
            $buf .= '  ! ' . $m . "\n";
        }
    }

    $buf .= "\nRENDER-THREAD SURFACE (files referencing TemplateRenderer / TemplateViewContextProvider)\n" . str_repeat('-', 60) . "\n";
    $buf .= $threadRefs === []
        ? "  (none found)\n"
        : implode("\n", array_map(static fn (string $p): string => '  - ' . $p, $threadRefs)) . "\n";

    $buf .= "\nREMAINING \$_SERVER['HTTP_HOST'] READS (to remove in 1.34b)\n" . str_repeat('-', 60) . "\n";
    $buf .= $serverHostRefs === []
        ? "  (none found)\n"
        : implode("\n", array_map(static fn (string $p): string => '  - ' . $p, $serverHostRefs)) . "\n";

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

    fwrite(STDOUT, "Site unification dump written to: {$out}\n");
    fwrite(STDOUT, '  Files dumped          : ' . $dumped . "\n");
    fwrite(STDOUT, '  Render-thread refs    : ' . count($threadRefs) . "\n");
    fwrite(STDOUT, "  \$_SERVER host reads   : " . count($serverHostRefs) . "\n");
    fwrite(STDOUT, '  Missing targets       : ' . count($missing) . "\n");

    return 0;
}

exit(main($argv));
