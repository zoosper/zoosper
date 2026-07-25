<?php

declare(strict_types=1);

/**
 * Phase 1.76 assisted site lookup migration helper.
 *
 * Dry-run by default. Pass --apply to write changes.
 *
 * Safety rules:
 *  - never touches page hot-path files (PageController/ContentPageController/PageRenderer);
 *  - only migrates files that reference the concrete SiteRepository as a clean
 *    constructor type-hint and do NOT already use SiteLookupInterface;
 *  - performs a conservative swap of the import + constructor type-hint only;
 *  - anything ambiguous is reported as "manual" and left untouched.
 *
 * The helper intentionally does not rewrite call sites beyond the type-hint,
 * because SiteLookupInterface is expected to expose the same read methods the
 * migrated consumers rely on. Any consumer needing more is left for manual work.
 */

$root = dirname(__DIR__);
$appDir = $root . '/app';

$apply = in_array('--apply', $argv, true);
$mode = $apply ? 'apply' : 'dry-run';

$normalise = static fn (string $p): string => str_replace('\\', '/', $p);

$isApprovedArea = static function (string $relative): bool {
    return preg_match('#(^|/)config/(controllers|services)\.php$#', $relative) === 1
        || preg_match('#(^|/)tests?/#', $relative) === 1
        || preg_match('#(^|/)tools?/#', $relative) === 1
        || preg_match('#(^|/)docs?/#', $relative) === 1
        || preg_match('#(^|/)database/#', $relative) === 1
        || preg_match('#(^|/)migrations?/#', $relative) === 1
        || preg_match('#(^|/)Repository/#', $relative) === 1
        || preg_match('#SiteRepository\.php$#', $relative) === 1
        || preg_match('#SiteResolver\.php$#', $relative) === 1
        || preg_match('#/(Admin/Controller|src/Admin/Controller)/#', $relative) === 1
        || preg_match('#OptionsProvider\.php$#', $relative) === 1;
};

$isHotPath = static function (string $relative): bool {
    return preg_match('#/(PageController|ContentPageController|PageRenderer)\.php$#', $relative) === 1;
};

$changed = [];
$skippedManual = [];
$warnings = [];
$scannedFiles = 0;

if (!is_dir($appDir)) {
    $warnings[] = 'The app directory was not found; helper had nothing to migrate.';
} else {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($appDir, FilesystemIterator::SKIP_DOTS),
            static function (SplFileInfo $file): bool {
                $path = str_replace('\\', '/', $file->getPathname());
                if ($file->isDir()) {
                    return !preg_match('#/(vendor|node_modules|var|cache|generated|storage|public/build)$#', $path);
                }
                return $file->getExtension() === 'php';
            }
        )
    );

    foreach ($iterator as $file) {
        if (!$file instanceof SplFileInfo || !$file->isFile()) {
            continue;
        }

        $path = $normalise($file->getPathname());
        $relative = ltrim(str_replace($normalise($root), '', $path), '/');
        $contents = file_get_contents($path);

        if ($contents === false) {
            $warnings[] = "Could not read {$relative}.";
            continue;
        }

        $scannedFiles++;

        $mentionsConcrete = preg_match('/\bSiteRepository\b/', $contents) === 1;
        $mentionsInterface = preg_match('/\bSiteLookupInterface\b/', $contents) === 1;

        if (!$mentionsConcrete || $mentionsInterface || $isApprovedArea($relative) || $isHotPath($relative)) {
            continue;
        }

        // Discover the fully-qualified SiteRepository import so we can build the
        // matching interface import in the same namespace family.
        if (!preg_match('/^use\s+([A-Za-z0-9_\\\\]+\\\\)SiteRepository;\s*$/m', $contents, $useMatch)) {
            $skippedManual[] = [
                'file' => $relative,
                'reason' => 'No clean single "use ...\\SiteRepository;" import found.',
            ];
            continue;
        }

        $importNamespace = $useMatch[1];
        $interfaceFqcn = $importNamespace . 'SiteLookupInterface';

        // Require a clean constructor type-hint occurrence to migrate.
        if (preg_match('/\bSiteRepository\s+\$[a-zA-Z_][a-zA-Z0-9_]*/', $contents) !== 1) {
            $skippedManual[] = [
                'file' => $relative,
                'reason' => 'SiteRepository referenced but not as a clean constructor type-hint.',
            ];
            continue;
        }

        // Guard: if SiteRepository is used in more places than the import + a
        // single type-hint (e.g. static calls, new SiteRepository()), treat as manual.
        $occurrences = preg_match_all('/\bSiteRepository\b/', $contents);
        if ($occurrences > 2) {
            $skippedManual[] = [
                'file' => $relative,
                'reason' => "Multiple SiteRepository usages ({$occurrences}); manual migration recommended.",
            ];
            continue;
        }

        $updated = preg_replace(
            '/^use\s+' . preg_quote($importNamespace, '/') . 'SiteRepository;\s*$/m',
            'use ' . $interfaceFqcn . ';',
            $contents
        );
        $updated = preg_replace(
            '/\bSiteRepository(\s+\$[a-zA-Z_][a-zA-Z0-9_]*)/',
            'SiteLookupInterface$1',
            (string) $updated
        );

        if ($updated === $contents || $updated === null) {
            $skippedManual[] = [
                'file' => $relative,
                'reason' => 'Swap produced no change; manual review recommended.',
            ];
            continue;
        }

        $changed[] = [
            'file' => $relative,
            'interface' => $interfaceFqcn,
        ];

        if ($apply) {
            file_put_contents($path, $updated);
        }
    }
}

echo "## Assisted Site Lookup Migration\n\n";
echo 'Mode: ' . $mode . "\n";
echo 'Generated: ' . gmdate('c') . "\n";
echo 'Scanned: ' . $scannedFiles . " PHP file(s)\n";
echo 'Migrated' . ($apply ? '' : ' (planned)') . ': ' . count($changed) . "\n";
echo 'Skipped (manual): ' . count($skippedManual) . "\n";
echo 'Warnings: ' . count($warnings) . "\n\n";

if ($changed !== []) {
    echo ($apply ? "### Migrated\n" : "### Planned migrations\n");
    foreach ($changed as $c) {
        echo "- {$c['file']} -> {$c['interface']}\n";
    }
    echo "\n";
}

if ($skippedManual !== []) {
    echo "### Skipped (manual review)\n";
    foreach ($skippedManual as $s) {
        echo "- {$s['file']}: {$s['reason']}\n";
    }
    echo "\n";
}

if ($warnings !== []) {
    echo "### Warnings\n";
    foreach ($warnings as $w) {
        echo "- {$w}\n";
    }
    echo "\n";
}

if (!$apply) {
    echo "Dry-run only. Re-run with --apply to write these changes.\n";
}
