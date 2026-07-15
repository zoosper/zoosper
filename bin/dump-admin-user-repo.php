<?php

declare(strict_types=1);

/**
 * Zoosper CMS - Admin User Repository Diagnostic Dumper
 * =====================================================
 *
 * Dumps the files needed to diagnose an HY093 (bound variables vs tokens
 * mismatch) when saving an admin user, plus the logging/exception-handling
 * config so we can also fix why the exception is not written to file.
 *
 * Usage:
 *   php bin/dump-admin-user-repo.php
 *   php bin/dump-admin-user-repo.php --root=/path --out=dump.txt
 *
 * PCI note: reads source only; never reads .env or secrets.
 */

const MAX_BYTES = 256 * 1024;
const SKIP_DIRS = ['vendor', 'node_modules', '.git', 'storage', 'var'];

/** Exact targets to dump if present. */
const TARGETS = [
    'app/zoosper-auth/src/Repository/AdminUserRepository.php',
    'app/zoosper-auth/src/Entity/Save/AdminUserSaveDataFactory.php',
    'config/logging.php',
    'database/schema/admin_user_locale.php',
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
    $out  = $args['out'] ?? 'zoosper-admin-user-repo-dump.txt';

    $rel = static fn (string $p): string => ltrim(str_replace($root, '', $p), '/\\');

    $toDump = [];   // rel => absolute
    $missing = [];

    foreach (TARGETS as $t) {
        $full = $root . DIRECTORY_SEPARATOR . $t;
        if (is_file($full)) {
            $toDump[$t] = $full;
        } else {
            $missing[] = $t;
        }
    }

    // Migrations relating to auth / admin_user / user_role (up to 4).
    $migCount = 0;
    foreach (recurse($root . '/database/migrations', $root) as $path) {
        $name = strtolower(basename($path));
        if ((str_contains($name, 'auth') || str_contains($name, 'admin_user') || str_contains($name, 'user_role')) && $migCount < 4) {
            $toDump[$rel($path)] = $path;
            $migCount++;
        }
    }

    // RoleRepository (1).
    foreach (recurse($root . '/app', $root) as $path) {
        if (basename($path) === 'RoleRepository.php') {
            $toDump[$rel($path)] = $path;
            break;
        }
    }

    // Core Logger / ExceptionHandler / ErrorHandler (up to 3).
    $logCount = 0;
    foreach (recurse($root . '/app/zoosper-core', $root) as $path) {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'php') {
            continue;
        }
        $b = basename($path);
        if ((str_contains($b, 'Logger') || str_contains($b, 'ExceptionHandler') || str_contains($b, 'ErrorHandler')) && $logCount < 3) {
            $toDump[$rel($path)] = $path;
            $logCount++;
        }
    }

    ksort($toDump);

    $buf  = "ZOOSPER CMS - ADMIN USER REPO DIAGNOSTIC DUMP\n";
    $buf .= str_repeat('=', 60) . "\n";
    $buf .= 'Generated : ' . date('c') . "\n";
    $buf .= 'Repo root : ' . $root . "\n";
    $buf .= 'Dumped    : ' . count($toDump) . " file(s)\n";
    $buf .= "PCI note  : source only; .env not read.\n";
    $buf .= str_repeat('=', 60) . "\n";

    if ($missing !== []) {
        $buf .= "\nMISSING TARGETS\n" . str_repeat('-', 60) . "\n";
        foreach ($missing as $m) {
            $buf .= '  ! ' . $m . "\n";
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

    fwrite(STDOUT, "Admin user repo dump written to: {$out}\n");
    fwrite(STDOUT, '  Files dumped   : ' . $dumped . "\n");
    if ($missing !== []) {
        fwrite(STDOUT, '  Missing targets: ' . count($missing) . "\n");
    }

    return 0;
}

exit(main($argv));
