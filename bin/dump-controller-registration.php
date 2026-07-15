<?php

declare(strict_types=1);

/**
 * Zoosper CMS - Controller Registration Locator
 * =============================================
 * Phase 1.25.
 *
 * Finds where PageAdminController / UserAdminController are REGISTERED (their DI
 * factory / route binding) so the entity save lifecycle runner can be injected
 * into them. Dumps the full contents of config-like files that reference the
 * controllers, and lists (path only) any other referencing files.
 *
 * Usage:
 *   php bin/dump-controller-registration.php
 *   php bin/dump-controller-registration.php --root=/path --out=dump.txt
 *
 * PCI note: reads source only; never reads .env or secrets.
 */

const NEEDLES = ['PageAdminController', 'UserAdminController'];
const SKIP_DIRS = ['vendor', 'node_modules', '.git', 'storage', 'var'];
const MAX_BYTES = 256 * 1024;

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

/** @param array<int,string> $argv */
function main(array $argv): int
{
    $args = parseArgs($argv);
    $default = dirname(__DIR__);
    $root = $args['root'] ?? (is_dir($default) ? $default : getcwd());
    $root = rtrim((string) (realpath($root) ?: $root), '/\\');
    $out  = $args['out'] ?? 'zoosper-controller-registration-dump.txt';

    $rel = static fn (string $p): string => ltrim(str_replace($root, '', $p), '/\\');

    $appDir = $root . DIRECTORY_SEPARATOR . 'app';
    $referencing = [];   // rel path => is_config
    if (is_dir($appDir)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appDir, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $info) {
            /** @var SplFileInfo $info */
            if (!$info->isFile() || strtolower($info->getExtension()) !== 'php') {
                continue;
            }
            $path = $info->getPathname();
            $r = $rel($path);
            if (isSkipped($r)) {
                continue;
            }
            // Skip the controller class definitions themselves.
            if (str_ends_with($r, 'Controller/PageAdminController.php') || str_ends_with($r, 'Controller/UserAdminController.php')) {
                continue;
            }
            $size = (int) $info->getSize();
            if ($size > MAX_BYTES) {
                continue;
            }
            $contents = (string) file_get_contents($path);
            $hit = false;
            foreach (NEEDLES as $needle) {
                if (str_contains($contents, $needle)) {
                    $hit = true;
                    break;
                }
            }
            if ($hit) {
                $isConfig = str_contains('/' . str_replace('\\', '/', $r) . '/', '/config/');
                $referencing[$r] = $isConfig;
            }
        }
    }

    ksort($referencing);

    $buf  = "ZOOSPER CMS - CONTROLLER REGISTRATION DUMP\n";
    $buf .= str_repeat('=', 60) . "\n";
    $buf .= 'Generated : ' . date('c') . "\n";
    $buf .= 'Repo root : ' . $root . "\n";
    $buf .= 'Matches   : ' . count($referencing) . " file(s) reference the controllers\n";
    $buf .= "PCI note  : source only; .env not read.\n";
    $buf .= str_repeat('=', 60) . "\n\n";

    $buf .= "REFERENCING FILES\n" . str_repeat('-', 60) . "\n";
    foreach ($referencing as $r => $isConfig) {
        $buf .= '  ' . ($isConfig ? '[config] ' : '[other]  ') . $r . "\n";
    }

    $buf .= "\n" . str_repeat('=', 60) . "\nCONFIG FILE CONTENTS (full)\n" . str_repeat('=', 60) . "\n";
    $dumped = 0;
    foreach ($referencing as $r => $isConfig) {
        if (!$isConfig) {
            continue;
        }
        $full = $root . DIRECTORY_SEPARATOR . $r;
        $contents = (string) file_get_contents($full);
        $size = strlen($contents);
        $buf .= "\n\n==== FILE: {$r} ({$size} bytes) ====\n";
        $buf .= $contents;
        if (!str_ends_with($contents, "\n")) {
            $buf .= "\n";
        }
        $buf .= "==== END FILE: {$r} ====\n";
        $dumped++;
    }

    if ($dumped === 0) {
        $buf .= "\n(No config-path files matched. See the REFERENCING FILES list above;\n";
        $buf .= " the controllers may be registered in a controllers.php outside a\n";
        $buf .= " /config/ directory - paste whichever file registers them.)\n";
    }

    file_put_contents($out, $buf);

    fwrite(STDOUT, "Controller registration dump written to: {$out}\n");
    fwrite(STDOUT, '  Referencing files : ' . count($referencing) . "\n");
    fwrite(STDOUT, '  Config files dumped: ' . $dumped . "\n");
    if ($dumped === 0 && $referencing !== []) {
        fwrite(STDOUT, "  NOTE: no /config/ match - paste the referencing file(s) listed in the dump.\n");
    }

    return 0;
}

exit(main($argv));
