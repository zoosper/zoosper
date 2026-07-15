<?php

declare(strict_types=1);

/**
 * Zoosper CMS - Test Context Dumper
 * =================================
 *
 * Purpose
 * -------
 * Dumps the FULL contents of the source files needed to correctly finalise the
 * Phase 1.21 Pest test files (i.e. to resolve every `// ADJUST` placeholder with
 * the real class names, namespaces, method signatures, and container bootstrap).
 *
 * Run it, then hand the resulting text file back to Copilot so it can generate
 * corrected, ready-to-run test files - no manual find/replace of ADJUST markers.
 *
 * Usage
 * -----
 *   php bin/dump-test-context.php
 *   php bin/dump-test-context.php --root=/path/to/zoosper --out=context.txt
 *
 * Options
 * -------
 *   --root=<dir>   Repository root to scan. Defaults to the parent of bin/,
 *                  falling back to the current working directory.
 *   --out=<file>   Output file. Default: zoosper-test-context-dump.txt
 *
 * PCI / security note
 * -------------------
 * This script only READS source files and never targets environment files
 * (.env and similar are intentionally NOT collected). Even so, review the dump
 * and redact anything sensitive before sharing it - never paste real secrets,
 * TOTP secrets, recovery codes, session tokens, SMTP credentials, or payment
 * data into a shared context file.
 */

const MAX_FILE_BYTES = 512 * 1024; // Skip any single file larger than 512 KB.

const SKIP_DIRS = [
    'vendor',
    'node_modules',
    '.git',
    'storage',
    '.phpunit.cache',
    'public/build',
    'dist',
];

/**
 * Target groups. Each entry is a list of basename patterns (exact names or
 * fnmatch globs) to locate anywhere under the repo. A handful of targets are
 * root-only or content-matched and handled specially in main().
 *
 * @var array<string, array<int, string>>
 */
const TARGET_GROUPS = [
    'Composer & config' => [
        'phpunit.xml',
        'pest.php',
        'vite.admin-editor.config.js',
    ],
    'Existing tests' => [
        'run.php',
        'TestCase.php',
        'Pest.php',
    ],
    'Entity save pipeline' => [
        'FieldDefinition.php',
        'FieldDefinitionRegistry.php',
        'EntityExtensionDataPersister.php',
    ],
    'Lifecycle events (Phase 1.20)' => [
        'EntitySaveEventDispatcher.php',
        'EntitySaveLifecycle.php',
        'EntitySave*Event*.php',
    ],
    'Block content' => [
        'BlockJsonValidator.php',
        'BlockJsonToHtmlRenderer.php',
    ],
    'Security / sanitisation' => [
        'HtmlSanitizer.php',
        '*Sanitizer.php',
    ],
    'Admin form extensibility' => [
        'AdminFormSectionProviderInterface.php',
        'AdminFormProviderRegistry.php',
        'AdminFormRenderer.php',
        'AdminFormProcessorInterface.php',
        'AdminFormProcessorRegistry.php',
    ],
    'Router & bootstrap' => [
        'Router.php',
        'Route.php',
        'ApplicationFactory.php',
        'ServiceContainer.php',
    ],
    'Example admin controllers' => [
        'PageAdminController.php',
        'UserAdminController.php',
    ],
];

/** Root-only files (matched only at the repo root, not recursively). */
const ROOT_ONLY = [
    'composer.json',
];

/**
 * Capped sampling groups: pattern => max number of matches to include.
 * Keeps the dump manageable for patterns that could match many files.
 *
 * @var array<string, array{group:string, pattern:string, limit:int}>
 */
const SAMPLED = [
    ['group' => 'Verification tooling (sample)', 'pattern' => 'run-verification-suite.php', 'limit' => 1],
    ['group' => 'Verification tooling (sample)', 'pattern' => 'verify-*.php',                'limit' => 5],
    ['group' => 'Example module wiring (sample)', 'pattern' => 'module.php',    'limit' => 3],
    ['group' => 'Example module wiring (sample)', 'pattern' => 'services.php',  'limit' => 2],
    ['group' => 'Example module wiring (sample)', 'pattern' => 'db_schema.php', 'limit' => 2],
    ['group' => 'Example module wiring (sample)', 'pattern' => 'admin_forms.php','limit' => 2],
    ['group' => 'Example module wiring (sample)', 'pattern' => 'api_routes.php', 'limit' => 2],
];

/** Content-matched group: dump up to N php files containing a needle. */
const CONTENT_MATCH = [
    ['group' => 'Security / sanitisation', 'needle' => 'HTMLPurifier', 'limit' => 5],
];

/**
 * Parse simple --key=value CLI arguments.
 *
 * @param  array<int, string>  $argv
 * @return array<string, string>
 */
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
 * Determine whether a path segment should be skipped.
 */
function isSkippedPath(string $relative): bool
{
    $relative = str_replace('\\', '/', $relative);
    foreach (SKIP_DIRS as $skip) {
        if ($relative === $skip
            || str_starts_with($relative, $skip . '/')
            || str_contains($relative, '/' . $skip . '/')
        ) {
            return true;
        }
    }
    return false;
}

/**
 * Recursively collect every readable file path under $root (skipping SKIP_DIRS).
 *
 * @return array<int, string> Absolute paths.
 */
function collectAllFiles(string $root): array
{
    $files = [];
    $it = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            static function (SplFileInfo $current) use ($root): bool {
                $rel = ltrim(str_replace($root, '', $current->getPathname()), '/\\');
                return !isSkippedPath($rel);
            }
        ),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($it as $info) {
        /** @var SplFileInfo $info */
        if ($info->isFile()) {
            $files[] = $info->getPathname();
        }
    }
    sort($files);
    return $files;
}

/**
 * Find files whose basename matches an fnmatch pattern.
 *
 * @param  array<int, string>  $allFiles
 * @return array<int, string>
 */
function findFiles(array $allFiles, string $pattern): array
{
    $matches = [];
    foreach ($allFiles as $path) {
        if (fnmatch($pattern, basename($path), FNM_CASEFOLD)) {
            $matches[] = $path;
        }
    }
    return $matches;
}

/**
 * Find up to $limit php files whose contents contain $needle.
 *
 * @param  array<int, string>  $allFiles
 * @return array<int, string>
 */
function findByContent(array $allFiles, string $needle, int $limit): array
{
    $matches = [];
    foreach ($allFiles as $path) {
        if (!str_ends_with(strtolower($path), '.php')) {
            continue;
        }
        $size = @filesize($path);
        if ($size === false || $size > MAX_FILE_BYTES) {
            continue;
        }
        $contents = @file_get_contents($path);
        if ($contents !== false && str_contains($contents, $needle)) {
            $matches[] = $path;
            if (count($matches) >= $limit) {
                break;
            }
        }
    }
    return $matches;
}

/**
 * Build a directory tree limited to $maxDepth levels below $root.
 */
function buildTree(string $root, int $maxDepth = 3): string
{
    $lines = [basename(rtrim($root, '/\\')) . '/'];
    $walk = function (string $dir, string $prefix, int $depth) use (&$walk, $root, $maxDepth, &$lines): void {
        if ($depth > $maxDepth) {
            return;
        }
        $entries = @scandir($dir) ?: [];
        $entries = array_values(array_filter($entries, static fn ($e) => $e !== '.' && $e !== '..'));

        $kept = [];
        foreach ($entries as $e) {
            $full = $dir . DIRECTORY_SEPARATOR . $e;
            $rel  = ltrim(str_replace($root, '', $full), '/\\');
            if (isSkippedPath($rel)) {
                continue;
            }
            $kept[] = $e;
        }
        sort($kept);
        $count = count($kept);
        foreach ($kept as $i => $e) {
            $full   = $dir . DIRECTORY_SEPARATOR . $e;
            $isLast = ($i === $count - 1);
            $branch = $isLast ? '`-- ' : '|-- ';
            $isDir  = is_dir($full);
            $lines[] = $prefix . $branch . $e . ($isDir ? '/' : '');
            if ($isDir) {
                $childPrefix = $prefix . ($isLast ? '    ' : '|   ');
                $walk($full, $childPrefix, $depth + 1);
            }
        }
    };
    $walk($root, '', 1);
    return implode("\n", $lines);
}

/**
 * Entry point.
 *
 * @param  array<int, string>  $argv
 */
function main(array $argv): int
{
    $args = parseArgs($argv);

    // Resolve repo root: --root, else parent of bin/, else cwd.
    $default = dirname(__DIR__);
    $root    = $args['root'] ?? (is_dir($default) ? $default : getcwd());
    $root    = rtrim((string) realpath($root) ?: $root, '/\\');

    $out = $args['out'] ?? 'zoosper-test-context-dump.txt';

    fwrite(STDOUT, "Scanning repository: {$root}\n");

    $allFiles = collectAllFiles($root);

    // Collect matches grouped, de-duplicated, recording no-match patterns.
    $selected     = [];   // absolute path => group
    $noMatch      = [];   // "group: pattern" strings
    $tooLarge     = [];   // relative paths skipped for size

    $addMatch = static function (string $path, string $group) use (&$selected): void {
        if (!isset($selected[$path])) {
            $selected[$path] = $group;
        }
    };

    // Root-only files.
    foreach (ROOT_ONLY as $name) {
        $candidate = $root . DIRECTORY_SEPARATOR . $name;
        if (is_file($candidate)) {
            $addMatch($candidate, 'Composer & config');
        } else {
            $noMatch[] = 'Composer & config: ' . $name . ' (root)';
        }
    }

    // Standard target groups.
    foreach (TARGET_GROUPS as $group => $patterns) {
        foreach ($patterns as $pattern) {
            $found = findFiles($allFiles, $pattern);
            if ($found === []) {
                $noMatch[] = $group . ': ' . $pattern;
                continue;
            }
            foreach ($found as $f) {
                $addMatch($f, $group);
            }
        }
    }

    // Sampled (capped) patterns.
    foreach (SAMPLED as $s) {
        $found = array_slice(findFiles($allFiles, $s['pattern']), 0, $s['limit']);
        if ($found === []) {
            $noMatch[] = $s['group'] . ': ' . $s['pattern'] . ' (sampled)';
            continue;
        }
        foreach ($found as $f) {
            $addMatch($f, $s['group']);
        }
    }

    // Content-matched patterns.
    foreach (CONTENT_MATCH as $c) {
        $found = findByContent($allFiles, $c['needle'], $c['limit']);
        if ($found === []) {
            $noMatch[] = $c['group'] . ': files containing "' . $c['needle'] . '"';
            continue;
        }
        foreach ($found as $f) {
            $addMatch($f, $c['group']);
        }
    }

    // Build output.
    $rel = static fn (string $p): string => ltrim(str_replace($root, '', $p), '/\\');

    // Group selected files for the summary.
    $byGroup = [];
    foreach ($selected as $path => $group) {
        $byGroup[$group][] = $rel($path);
    }
    ksort($byGroup);
    foreach ($byGroup as &$list) {
        sort($list);
    }
    unset($list);

    $buf  = "ZOOSPER CMS - TEST CONTEXT DUMP\n";
    $buf .= str_repeat('=', 70) . "\n";
    $buf .= 'Generated : ' . date('c') . "\n";
    $buf .= 'Repo root : ' . $root . "\n";
    $buf .= 'Files     : ' . count($selected) . " dumped\n";
    $buf .= "Purpose   : Provide full source so Phase 1.21 test // ADJUST markers\n";
    $buf .= "            can be resolved into correct, ready-to-run tests.\n";
    $buf .= str_repeat('=', 70) . "\n\n";

    $buf .= "SUMMARY - FILES TO BE DUMPED\n";
    $buf .= str_repeat('-', 70) . "\n";
    foreach ($byGroup as $group => $list) {
        $buf .= "\n[" . $group . "]\n";
        foreach ($list as $r) {
            $buf .= '  - ' . $r . "\n";
        }
    }

    $buf .= "\n\nPATTERNS WITH NO MATCH (missing / renamed - tell Copilot)\n";
    $buf .= str_repeat('-', 70) . "\n";
    if ($noMatch === []) {
        $buf .= "  (none - every pattern matched at least one file)\n";
    } else {
        sort($noMatch);
        foreach ($noMatch as $nm) {
            $buf .= '  ! ' . $nm . "\n";
        }
    }

    $buf .= "\n\nDIRECTORY TREE (top 3 levels, skipped dirs omitted)\n";
    $buf .= str_repeat('-', 70) . "\n";
    $buf .= buildTree($root, 3) . "\n";

    $buf .= "\n\n" . str_repeat('=', 70) . "\n";
    $buf .= "FILE CONTENTS\n";
    $buf .= str_repeat('=', 70) . "\n";

    $dumped = 0;
    foreach (array_keys($selected) as $path) {
        $size = @filesize($path);
        if ($size === false) {
            continue;
        }
        if ($size > MAX_FILE_BYTES) {
            $tooLarge[] = $rel($path) . ' (' . $size . ' bytes)';
            continue;
        }
        $contents = @file_get_contents($path);
        if ($contents === false) {
            continue;
        }
        $lines = substr_count($contents, "\n") + 1;
        $buf .= "\n\n==== FILE: " . $rel($path)
             . ' (' . $size . ' bytes, ' . $lines . " lines) ====\n";
        $buf .= $contents;
        if (!str_ends_with($contents, "\n")) {
            $buf .= "\n";
        }
        $buf .= "==== END FILE: " . $rel($path) . " ====\n";
        $dumped++;
    }

    if ($tooLarge !== []) {
        $buf .= "\n\nSKIPPED - TOO LARGE (> 512 KB)\n";
        $buf .= str_repeat('-', 70) . "\n";
        foreach ($tooLarge as $t) {
            $buf .= '  ~ ' . $t . "\n";
        }
    }

    file_put_contents($out, $buf);

    fwrite(STDOUT, "\nDone.\n");
    fwrite(STDOUT, '  Output file : ' . $out . "\n");
    fwrite(STDOUT, '  Files dumped: ' . $dumped . "\n");
    fwrite(STDOUT, '  No-match    : ' . count($noMatch) . " pattern(s)\n");
    fwrite(STDOUT, '  Output size : ' . round(strlen($buf) / 1024, 1) . " KB\n");
    if ($noMatch !== []) {
        fwrite(STDOUT, "\n  NOTE: some patterns had no match - share the SUMMARY so\n");
        fwrite(STDOUT, "        Copilot knows which classes were renamed/missing.\n");
    }

    return 0;
}

exit(main($argv));
