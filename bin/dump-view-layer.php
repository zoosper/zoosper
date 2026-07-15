<?php

declare(strict_types=1);

/**
 * Zoosper CMS - View Layer Locator
 * ================================
 * Phase 1.26.
 *
 * Dumps the full contents of the files needed to design the controller view
 * extraction (renderers, layout, form renderer, template config), locates the
 * `theme.admin_template_renderer` service, and lists (path only) every existing
 * .latte template plus views/templates directories so the naming/namespace
 * convention is clear.
 *
 * Usage:
 *   php bin/dump-view-layer.php
 *   php bin/dump-view-layer.php --root=/path --out=zoosper-view-layer-dump.txt
 *
 * PCI note: reads source only; never reads .env or secrets.
 */

const MAX_BYTES = 256 * 1024;
const SKIP_DIRS = ['vendor', 'node_modules', '.git', 'storage'];

/** Exact files to dump if present. */
const TARGETS = [
    'app/zoosper-admin/src/UI/AdminViewRenderer.php',
    'app/zoosper-admin/src/Layout/AdminLayout.php',
    'app/zoosper-admin/src/UI/AdminComponentRenderer.php',
    'app/zoosper-admin/src/Form/AdminFormRenderer.php',
    'config/template.php',
    'config/assets.php',
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

/** @param array<int,string> $argv */
function main(array $argv): int
{
    $args = parseArgs($argv);
    $default = dirname(__DIR__);
    $root = $args['root'] ?? (is_dir($default) ? $default : getcwd());
    $root = rtrim((string) (realpath($root) ?: $root), '/\\');
    $out  = $args['out'] ?? 'zoosper-view-layer-dump.txt';

    $rel = static fn (string $p): string => ltrim(str_replace($root, '', $p), '/\\');

    $toDump = [];   // rel => absolute
    $missing = [];

    // Exact targets.
    foreach (TARGETS as $t) {
        $full = $root . DIRECTORY_SEPARATOR . $t;
        if (is_file($full)) {
            $toDump[$t] = $full;
        } else {
            $missing[] = $t;
        }
    }

    // Theme renderer classes (name contains TemplateRenderer or Latte).
    $themeMatches = 0;
    foreach (recurse($root . '/app/zoosper-theme/src', $root) as $path) {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') {
            continue;
        }
        $name = basename($path);
        if ((str_contains($name, 'TemplateRenderer') || str_contains($name, 'Latte')) && $themeMatches < 4) {
            $toDump[$rel($path)] = $path;
            $themeMatches++;
        }
    }

    // Locate 'theme.admin_template_renderer' in any app config/services.php.
    $svcMatches = 0;
    foreach (recurse($root . '/app', $root) as $path) {
        if (basename($path) !== 'services.php' || !str_contains($path, DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR)) {
            continue;
        }
        $contents = (string) @file_get_contents($path);
        if (str_contains($contents, 'theme.admin_template_renderer') && $svcMatches < 3) {
            $toDump[$rel($path)] = $path;
            $svcMatches++;
        }
    }

    // Discovery (paths only): all .latte files under app/ and themes/.
    $latte = [];
    foreach (['app', 'themes'] as $sub) {
        foreach (recurse($root . DIRECTORY_SEPARATOR . $sub, $root) as $path) {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'latte') {
                $latte[] = $rel($path);
            }
        }
    }
    sort($latte);

    // Discovery (paths only): views/templates directories.
    $viewDirs = [];
    foreach (['app', 'themes'] as $sub) {
        $baseDir = $root . DIRECTORY_SEPARATOR . $sub;
        if (!is_dir($baseDir)) {
            continue;
        }
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $info) {
            /** @var SplFileInfo $info */
            if ($info->isDir() && in_array($info->getFilename(), ['views', 'templates'], true)) {
                $r = $rel($info->getPathname());
                if (!isSkipped($r)) {
                    $viewDirs[] = $r;
                }
            }
        }
    }
    sort($viewDirs);

    // Build output.
    ksort($toDump);
    $buf  = "ZOOSPER CMS - VIEW LAYER DUMP (Phase 1.26)\n";
    $buf .= str_repeat('=', 60) . "\n";
    $buf .= 'Generated : ' . date('c') . "\n";
    $buf .= 'Repo root : ' . $root . "\n";
    $buf .= 'Dumped    : ' . count($toDump) . " file(s)\n";
    $buf .= 'Latte     : ' . count($latte) . " template(s) found\n";
    $buf .= "PCI note  : source only; .env not read.\n";
    $buf .= str_repeat('=', 60) . "\n";

    if ($missing !== []) {
        $buf .= "\nMISSING TARGETS (renamed or absent)\n" . str_repeat('-', 60) . "\n";
        foreach ($missing as $m) {
            $buf .= '  ! ' . $m . "\n";
        }
    }

    $buf .= "\nVIEWS / TEMPLATES DIRECTORIES\n" . str_repeat('-', 60) . "\n";
    $buf .= $viewDirs === [] ? "  (none found)\n" : implode("\n", array_map(static fn ($d) => '  - ' . $d, $viewDirs)) . "\n";

    $buf .= "\nEXISTING .latte TEMPLATES (paths only)\n" . str_repeat('-', 60) . "\n";
    $buf .= $latte === [] ? "  (none found)\n" : implode("\n", array_map(static fn ($t) => '  - ' . $t, $latte)) . "\n";

    $buf .= "\n" . str_repeat('=', 60) . "\nFILE CONTENTS\n" . str_repeat('=', 60) . "\n";
    $dumped = 0;
    foreach ($toDump as $r => $full) {
        $size = (int) filesize($full);
        if ($size > MAX_BYTES) {
            $buf .= "\n\n==== FILE: {$r} (SKIPPED, {$size} bytes > limit) ====\n";
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

    fwrite(STDOUT, "View layer dump written to: {$out}\n");
    fwrite(STDOUT, '  Files dumped   : ' . $dumped . "\n");
    fwrite(STDOUT, '  Latte templates: ' . count($latte) . "\n");
    fwrite(STDOUT, '  View dirs      : ' . count($viewDirs) . "\n");
    if ($missing !== []) {
        fwrite(STDOUT, '  Missing targets: ' . count($missing) . " (see dump header)\n");
    }

    return 0;
}

exit(main($argv));
