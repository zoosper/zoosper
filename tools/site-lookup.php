<?php

declare(strict_types=1);

/**
 * Phase 1.77 unified site lookup tooling CLI.
 *
 * Consolidates three previously separate scripts into one dispatcher:
 *   audit     - tuned V2 boundary drift guard (read-only)
 *   snapshot  - durable migration candidate tracker (read-only)
 *   migrate   - assisted migration (dry-run by default; --apply to write)
 *
 * Usage:
 *   php8.5 tools/site-lookup.php <audit|snapshot|migrate> [--apply]
 */

$root = dirname(__DIR__);
$appDir = $root . '/app';

$normalise = static fn (string $p): string => str_replace('\\', '/', $p);

/**
 * Shared file iterator over application PHP source.
 *
 * @return Generator<array{path:string,relative:string,contents:string}>
 */
$iterateSource = static function () use ($root, $appDir, $normalise): Generator {
    if (!is_dir($appDir)) {
        return;
    }

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
            continue;
        }
        yield ['path' => $path, 'relative' => $relative, 'contents' => $contents];
    }
};

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

$isLegacyFallbackApprovedArea = static function (string $relative): bool {
    return preg_match('#(^|/)config/services\.php$#', $relative) === 1
        || preg_match('#(^|/)tests?/#', $relative) === 1
        || preg_match('#CurrentSiteContext\.php$#', $relative) === 1;
};

$isHotPath = static function (string $relative): bool {
    return preg_match('#/(PageController|ContentPageController|PageRenderer)\.php$#', $relative) === 1;
};

$ensureDir = static function (string $dir): void {
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        fwrite(STDERR, "Unable to create directory: {$dir}\n");
        exit(1);
    }
};

/* ------------------------------------------------------------------ audit */
$runAudit = static function () use ($root, $iterateSource, $isApprovedArea, $isLegacyFallbackApprovedArea, $isHotPath, $ensureDir): int {
    $docsReportDir = $root . '/docs/reports';
    $reportPath = $docsReportDir . '/site-lookup-boundary-drift.json';

    $errors = [];
    $migrationCandidates = [];
    $warnings = [];
    $observations = [];
    $scannedFiles = 0;

    foreach ($iterateSource() as $entry) {
        $relative = $entry['relative'];
        $contents = $entry['contents'];
        $scannedFiles++;

        $mentionsConcrete = preg_match('/\bSiteRepository\b/', $contents) === 1;
        $mentionsInterface = preg_match('/\bSiteLookupInterface\b/', $contents) === 1;

        if ($mentionsConcrete && !$isApprovedArea($relative) && !$mentionsInterface) {
            $migrationCandidates[] = [
                'file' => $relative,
                'issue' => 'Concrete SiteRepository reference outside the currently approved infrastructure/admin areas.',
                'suggestion' => 'Consider migrating this consumer to SiteLookupInterface in a future cleanup phase if it is not intentionally repository-owned.',
            ];
        }

        if (preg_match('/\bCurrentSiteContext\b/', $contents) === 1 && !$isLegacyFallbackApprovedArea($relative)) {
            $warnings[] = "{$relative} references CurrentSiteContext; verify it is not being used as a fallback source of truth.";
        }

        if ($isHotPath($relative) && preg_match('/\bSiteResolver\b/', $contents) === 1) {
            $errors[] = [
                'file' => $relative,
                'issue' => 'Page rendering/controller hot path references SiteResolver.',
                'suggestion' => 'Use the request-carried site context and already-bound site lookup service instead of re-resolving site context.',
            ];
        }
    }

    $ensureDir($docsReportDir);

    $observations[] = "Scanned {$scannedFiles} PHP application file(s).";
    $observations[] = 'This audit is read-only and does not modify application source.';
    $observations[] = 'Concrete SiteRepository references outside approved areas are migration candidates, not hard failures.';

    $report = [
        'phase' => '1.77-site-lookup-cli/audit',
        'generatedAt' => gmdate('c'),
        'errors' => $errors,
        'migrationCandidates' => $migrationCandidates,
        'warnings' => $warnings,
        'observations' => $observations,
    ];
    file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

    echo "## Site Lookup Boundary Drift Guard Audit\n\n";
    echo 'Generated: ' . $report['generatedAt'] . "\n";
    echo 'Errors: ' . count($errors) . "\n";
    echo 'Migration Candidates: ' . count($migrationCandidates) . "\n";
    echo 'Warnings: ' . count($warnings) . "\n";
    echo 'Observations: ' . count($observations) . "\n";
    echo "Report: docs/reports/site-lookup-boundary-drift.json\n\n";

    if ($errors !== []) {
        echo "### Errors\n";
        foreach ($errors as $e) {
            echo "- {$e['file']}: {$e['issue']} {$e['suggestion']}\n";
        }
        echo "\n";
    }
    if ($migrationCandidates !== []) {
        echo "### Migration Candidates\n";
        foreach ($migrationCandidates as $c) {
            echo "- {$c['file']}: {$c['issue']} {$c['suggestion']}\n";
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
    if ($observations !== []) {
        echo "### Observations\n";
        foreach ($observations as $o) {
            echo "- {$o}\n";
        }
    }

    return $errors === [] ? 0 : 1;
};

/* --------------------------------------------------------------- snapshot */
$runSnapshot = static function () use ($root, $iterateSource, $isApprovedArea, $isHotPath, $ensureDir): int {
    $docsDevDir = $root . '/docs/development';
    $docsReportDir = $root . '/docs/reports';
    $trackerPath = $docsDevDir . '/site-lookup-migration-candidates.md';
    $jsonPath = $docsReportDir . '/site-lookup-migration-candidates.json';

    $candidates = [];
    $warnings = [];
    $scannedFiles = 0;

    foreach ($iterateSource() as $entry) {
        $relative = $entry['relative'];
        $contents = $entry['contents'];
        $scannedFiles++;

        $mentionsConcrete = preg_match('/\bSiteRepository\b/', $contents) === 1;
        $mentionsInterface = preg_match('/\bSiteLookupInterface\b/', $contents) === 1;

        if ($mentionsConcrete && !$isApprovedArea($relative) && !$mentionsInterface) {
            $cleanCtorSwap = preg_match('/SiteRepository\s+\$[a-zA-Z_][a-zA-Z0-9_]*/', $contents) === 1
                && !$isHotPath($relative);
            $candidates[] = [
                'file' => $relative,
                'autoMigratable' => $cleanCtorSwap,
                'note' => $cleanCtorSwap
                    ? 'Clean constructor type-hint swap candidate for the assisted migrate command.'
                    : 'Manual review recommended before migrating.',
            ];
        }
    }

    $ensureDir($docsDevDir);
    $ensureDir($docsReportDir);

    $auto = array_values(array_filter($candidates, static fn (array $c): bool => $c['autoMigratable']));
    $manual = array_values(array_filter($candidates, static fn (array $c): bool => !$c['autoMigratable']));
    $generatedAt = gmdate('c');

    $md = "# Site Lookup Migration Candidates\n\n";
    $md .= "Generated: {$generatedAt}\n\n";
    $md .= "This tracker records consumers that still reference the concrete `SiteRepository` ";
    $md .= "outside approved infrastructure/admin areas, so migration work is durable and never forgotten.\n\n";
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
    $md .= "1. `php8.5 tools/site-lookup.php migrate` for a dry-run preview.\n";
    $md .= "2. Review the proposed swaps.\n";
    $md .= "3. `php8.5 tools/site-lookup.php migrate --apply` to migrate clean cases.\n";
    $md .= "4. Handle manual candidates deliberately in a later cleanup phase.\n";

    file_put_contents($trackerPath, $md);

    $json = [
        'phase' => '1.77-site-lookup-cli/snapshot',
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

    return 0;
};

/* ---------------------------------------------------------------- migrate */
$runMigrate = static function (bool $apply) use ($root, $iterateSource, $isApprovedArea, $isHotPath): int {
    $changed = [];
    $skippedManual = [];
    $warnings = [];
    $scannedFiles = 0;
    $mode = $apply ? 'apply' : 'dry-run';

    foreach ($iterateSource() as $entry) {
        $path = $entry['path'];
        $relative = $entry['relative'];
        $contents = $entry['contents'];
        $scannedFiles++;

        $mentionsConcrete = preg_match('/\bSiteRepository\b/', $contents) === 1;
        $mentionsInterface = preg_match('/\bSiteLookupInterface\b/', $contents) === 1;

        if (!$mentionsConcrete || $mentionsInterface || $isApprovedArea($relative) || $isHotPath($relative)) {
            continue;
        }

        if (!preg_match('/^use\s+([A-Za-z0-9_\\\\]+\\\\)SiteRepository;\s*$/m', $contents, $useMatch)) {
            $skippedManual[] = ['file' => $relative, 'reason' => 'No clean single "use ...\\SiteRepository;" import found.'];
            continue;
        }

        $importNamespace = $useMatch[1];
        $interfaceFqcn = $importNamespace . 'SiteLookupInterface';

        if (preg_match('/\bSiteRepository\s+\$[a-zA-Z_][a-zA-Z0-9_]*/', $contents) !== 1) {
            $skippedManual[] = ['file' => $relative, 'reason' => 'SiteRepository referenced but not as a clean constructor type-hint.'];
            continue;
        }

        $occurrences = preg_match_all('/\bSiteRepository\b/', $contents);
        if ($occurrences > 2) {
            $skippedManual[] = ['file' => $relative, 'reason' => "Multiple SiteRepository usages ({$occurrences}); manual migration recommended."];
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
            $skippedManual[] = ['file' => $relative, 'reason' => 'Swap produced no change; manual review recommended.'];
            continue;
        }

        $changed[] = ['file' => $relative, 'interface' => $interfaceFqcn];
        if ($apply) {
            file_put_contents($path, $updated);
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
    if (!$apply) {
        echo "Dry-run only. Re-run with --apply to write these changes.\n";
    }

    return 0;
};

/* ------------------------------------------------------------ dispatcher */
$command = $argv[1] ?? null;
$apply = in_array('--apply', $argv, true);

$usage = static function (): int {
    echo "Zoosper Site Lookup CLI\n\n";
    echo "Usage:\n";
    echo "  php8.5 tools/site-lookup.php audit\n";
    echo "  php8.5 tools/site-lookup.php snapshot\n";
    echo "  php8.5 tools/site-lookup.php migrate [--apply]\n";
    return 0;
};

exit(match ($command) {
    'audit' => $runAudit(),
    'snapshot' => $runSnapshot(),
    'migrate' => $runMigrate($apply),
    default => $usage(),
});



