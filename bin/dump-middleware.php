<?php

declare(strict_types=1);

/**
 * Zoosper CMS - Middleware Pipeline Diagnostic Dumper (Phase 1.33 planning).
 *
 * Collects the files needed to design a PSR-15-style middleware pipeline with
 * zero guessing: the router, HTTP application, request/response, the auth guard
 * and CSRF manager, how controllers are registered, and how routes are declared.
 *
 * Usage:
 *   php bin/dump-middleware.php
 *   php bin/dump-middleware.php --root=. --out=zoosper-middleware-dump.txt
 *
 * PCI note: reads source only; never reads .env or secrets.
 */

const MAX_BYTES = 262144; // 256 KB
const SKIP_DIRS = ['vendor', 'node_modules', '.git', 'storage', 'var'];

const TARGETS = [
    'app/zoosper-core/src/Routing/Router.php',
    'app/zoosper-core/src/Routing/ModuleRouteLoader.php',
    'app/zoosper-core/src/Routing/ControllerProviderLoader.php',
    'app/zoosper-core/src/Http/Application.php',
    'app/zoosper-core/src/Http/Request.php',
    'app/zoosper-core/src/Http/Response.php',
    'app/zoosper-auth/src/Service/SessionGuard.php',
    'app/zoosper-auth/src/Service/CsrfTokenManager.php',
    'app/zoosper-auth/config/controllers.php',
    'app/zoosper-page/config/controllers.php',
    'public/index.php',
];

const NAME_NEEDLES = ['Middleware', 'Guard', 'Csrf', 'Kernel'];

/**
 * @param array<int, string> $argv
 * @return array<string, string>
 */
function parse_args(array $argv): array
{
    $out = [];
    foreach (array_slice($argv, 1) as $arg) {
        if (preg_match('/^--([a-z0-9_-]+)=(.*)$/i', $arg, $m) === 1) {
            $out[$m[1]] = $m[2];
        }
    }
    return $out;
}

function is_skipped(string $relative): bool
{
    $relative = str_replace('\\', '/', $relative);
    foreach (SKIP_DIRS as $dir) {
        if (str_starts_with($relative, $dir . '/') || str_contains($relative, '/' . $dir . '/')) {
            return true;
        }
    }
    return false;
}

/**
 * @return list<string>
 */
function recurse_php(string $directory, string $root): array
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
        if (!is_skipped($relative)) {
            $found[] = $info->getPathname();
        }
    }
    return $found;
}

function name_matches(string $basename): bool
{
    foreach (NAME_NEEDLES as $needle) {
        if (str_contains($basename, $needle)) {
            return true;
        }
    }
    return false;
}

/**
 * @param array<int, string> $argv
 */
function main(array $argv): int
{
    $args = parse_args($argv);
    $default = dirname(__DIR__);
    $root = $args['root'] ?? (is_dir($default) ? $default : (string) getcwd());
    $root = rtrim((string) (realpath($root) ?: $root), '/\\');
    $out = $args['out'] ?? 'zoosper-middleware-dump.txt';

    $rel = static fn (string $path): string => ltrim(str_replace($root, '', $path), '/\\');

    /** @var array<string, string> $toDump  relative => absolute */
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

    // Discovery: any middleware/guard/csrf/kernel classes under app/.
    $extra = 0;
    foreach (recurse_php($root . '/app', $root) as $path) {
        if ($extra >= 8) {
            break;
        }
        if (name_matches(basename($path, '.php'))) {
            $relPath = $rel($path);
            if (!isset($toDump[$relPath])) {
                $toDump[$relPath] = $path;
                $extra++;
            }
        }
    }

    // Route declaration files (paths only).
    $routeFiles = [];
    foreach (recurse_php($root . '/app', $root) as $path) {
        $base = basename($path);
        if ($base === 'admin_routes.php' || $base === 'api_routes.php') {
            $routeFiles[] = $rel($path);
        }
    }
    sort($routeFiles);
    ksort($toDump);

    $buffer = "ZOOSPER CMS - MIDDLEWARE PIPELINE DUMP (Phase 1.33)\n";
    $buffer .= str_repeat('=', 60) . "\n";
    $buffer .= 'Generated : ' . date('c') . "\n";
    $buffer .= 'Repo root : ' . $root . "\n";
    $buffer .= 'Dumped    : ' . count($toDump) . " file(s)\n";
    $buffer .= "PCI note  : source only; .env not read.\n";
    $buffer .= str_repeat('=', 60) . "\n";

    if ($missing !== []) {
        $buffer .= "\nMISSING TARGETS (renamed or not present)\n" . str_repeat('-', 60) . "\n";
        foreach ($missing as $item) {
            $buffer .= '  ! ' . $item . "\n";
        }
    }

    $buffer .= "\nROUTE DECLARATION FILES (paths only)\n" . str_repeat('-', 60) . "\n";
    $buffer .= $routeFiles === []
        ? "  (none found)\n"
        : implode("\n", array_map(static fn (string $p): string => '  - ' . $p, $routeFiles)) . "\n";

    $buffer .= "\n" . str_repeat('=', 60) . "\nFILE CONTENTS\n" . str_repeat('=', 60) . "\n";

    $dumped = 0;
    foreach ($toDump as $relPath => $full) {
        $size = (int) filesize($full);
        if ($size > MAX_BYTES) {
            $buffer .= "\n\n==== FILE: {$relPath} (SKIPPED {$size} bytes > limit) ====\n";
            continue;
        }
        $contents = (string) file_get_contents($full);
        $buffer .= "\n\n==== FILE: {$relPath} ({$size} bytes) ====\n";
        $buffer .= $contents;
        if (!str_ends_with($contents, "\n")) {
            $buffer .= "\n";
        }
        $buffer .= "==== END FILE: {$relPath} ====\n";
        $dumped++;
    }

    file_put_contents($out, $buffer);

    fwrite(STDOUT, "Middleware dump written to: {$out}\n");
    fwrite(STDOUT, '  Files dumped : ' . $dumped . "\n");
    fwrite(STDOUT, '  Missing      : ' . count($missing) . "\n");

    return 0;
}

exit(main($argv));
