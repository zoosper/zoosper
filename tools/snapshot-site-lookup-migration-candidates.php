<?php

declare(strict_types=1);

/**
 * Phase 1.76 read-only snapshot of site lookup migration candidates.
 *
 * Mirrors the V2 boundary guard candidate detection, but its job is to make the
 * candidate list durable and trackable so cleanup work is never forgotten. It
 * writes both a human tracker (Markdown) and a machine snapshot (JSON).
 */

$root = dirname(__DIR__);
$appDir = $root . '/app';
$docsDevDir = $root . '/docs/development';
$docsReportDir = $root . '/docs/reports';
$trackerPath = $docsDevDir . '/site-lookup-migration-candidates.md';
$jsonPath = $docsReportDir . '/site-lookup-migration-candidates.json';

$candidates = [];
$warnings = [];
$scannedFiles = 0;

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

if (!is_dir($appDir)) {
    $warnings[] = 'The app directory was not found; snapshot could not scan application source.';
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

        if ($mentionsConcrete && !$isApprovedArea($relative) && !$mentionsInterface) {
            $cleanCtorSwap = preg_match('/SiteRepository\s+\$[a-zA-Z_][a-zA-Z0-9_]*/', $contents) === 1
                && $isHotPath($relative) === false;

            $candidates[] = [
                'file' => $relative,
                'autoMigratable' => $cleanCtorSwap,
                'note' => $cleanCtorSwap
                    ? 'Clean constructor type-hint swap candidate for the assisted helper.'
                    : 'Manual review recommended before migrating.',
            ];
        }
    }
}

if (!is_dir($docsDevDir) && !mkdir($docsDevDir, 0775, true) && !is_dir($docsDevDir)) {
    fwrite(STDERR, "Unable to create docs/development directory.\n");
    exit(1);
}
if (!is_dir($docsReportDir) && !mkdir($docsReportDir, 0775, true) && !is_dir($docsReportDir)) {
    fwrite(STDERR, "Unable to create docs/reports directory.\n");
    exit(1);
}

$auto = array_values(array_filter($candidates, static fn (array $c): bool => $c['autoMigratable']));
$manual = array_values(array_filter($candidates, static fn (array $c): bool => !$c['autoMigratable']));

$generatedAt = gmdate('c');

$md = "# Site Lookup Migration Candidates\n\n";
$md .= "Generated: {$generatedAt}\n\n";
$md .= "This tracker records consumers that still reference the concrete `SiteRepository` ";
$md .= "outside approved infrastructure/admin areas. It exists so migration work is durable ";
$md .= "and never forgotten between phases.\n\n";
$md .= "- Total candidates: " . count($candidates) . "\n";
$md .= "- Auto-migratable (clean constructor swap): " . count($auto) . "\n";
$md .= "- Manual review: " . count($manual) . "\n\n";

$md .= "## Auto-migratable candidates\n\n";
if ($auto === []) {
    $md .= "_None._\n\n";
} else {
    foreach ($auto as $c) {
        $md .= "- [ ] `{$c['file']}` — {$c['note']}\n";
    }
    $md .= "\n";
}

$md .= "## Manual review candidates\n\n";
if ($manual === []) {
    $md .= "_None._\n\n";
} else {
    foreach ($manual as $c) {
        $md .= "- [ ] `{$c['file']}` — {$c['note']}\n";
    }
    $md .= "\n";
}

if ($warnings !== []) {
    $md .= "## Warnings\n\n";
    foreach ($warnings as $w) {
        $md .= "- {$w}\n";
    }
    $md .= "\n";
}

$md .= "## How to progress this list\n\n";
$md .= "1. Run `php8.5 tools/assist-site-lookup-migration.php` for a dry-run preview.\n";
$md .= "2. Review the proposed swaps.\n";
$md .= "3. Run `php8.5 tools/assist-site-lookup-migration.php --apply` to migrate clean cases.\n";
$md .= "4. Handle manual candidates deliberately in a later cleanup phase.\n";

file_put_contents($trackerPath, $md);

$json = [
    'phase' => '1.76-site-lookup-migration-candidates',
    'generatedAt' => $generatedAt,
    'totals' => [
        'candidates' => count($candidates),
        'autoMigratable' => count($auto),
        'manual' => count($manual),
    ],
    'candidates' => $candidates,
    'warnings' => $warnings,
    'scannedFiles' => $scannedFiles,
];
file_put_contents($jsonPath, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

echo "## Site Lookup Migration Candidate Snapshot\n\n";
echo "Generated: {$generatedAt}\n";
echo 'Candidates: ' . count($candidates) . "\n";
echo 'Auto-migratable: ' . count($auto) . "\n";
echo 'Manual: ' . count($manual) . "\n";
echo "Tracker: docs/development/site-lookup-migration-candidates.md\n";
echo "Snapshot: docs/reports/site-lookup-migration-candidates.json\n";
