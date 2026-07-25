<?php

declare(strict_types=1);

/**
 * Phase 1.75 hotfix V2: read-only drift guard for the site lookup boundary.
 *
 * V1 was intentionally protective but too noisy: it failed on normal DI
 * configuration, admin CRUD controllers, option providers, and the concrete
 * site resolver/binding layer. V2 keeps hard failures for genuine hot-path
 * regressions and records broader concrete repository references as migration
 * candidates instead of blocking the build.
 */

$root = dirname(__DIR__);
$appDir = $root . '/app';
$docsReportDir = $root . '/docs/reports';
$reportPath = $docsReportDir . '/site-lookup-boundary-drift.json';

$errors = [];
$warnings = [];
$migrationCandidates = [];
$observations = [];
$scannedFiles = 0;

$normalisePath = static function (string $path): string {
    return str_replace('\\', '/', $path);
};

$isApprovedConcreteRepositoryArea = static function (string $relative): bool {
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

$isPageResolutionHotPath = static function (string $relative): bool {
    return preg_match('#/(PageController|ContentPageController|PageRenderer)\.php$#', $relative) === 1;
};

if (!is_dir($appDir)) {
    $warnings[] = 'The app directory was not found; audit could not scan application source.';
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

        $path = $normalisePath($file->getPathname());
        $relative = ltrim(str_replace($normalisePath($root), '', $path), '/');
        $contents = file_get_contents($path);

        if ($contents === false) {
            $warnings[] = "Could not read {$relative}.";
            continue;
        }

        $scannedFiles++;

        $mentionsConcreteRepository = preg_match('/\bSiteRepository\b/', $contents) === 1;
        $mentionsInterface = preg_match('/\bSiteLookupInterface\b/', $contents) === 1;

        if ($mentionsConcreteRepository && !$isApprovedConcreteRepositoryArea($relative) && !$mentionsInterface) {
            $migrationCandidates[] = [
                'file' => $relative,
                'issue' => 'Concrete SiteRepository reference outside the currently approved infrastructure/admin areas.',
                'suggestion' => 'Consider migrating this consumer to SiteLookupInterface in a future cleanup phase if it is not intentionally repository-owned.',
            ];
        }

        if (preg_match('/\bCurrentSiteContext\b/', $contents) === 1 && !$isLegacyFallbackApprovedArea($relative)) {
            $warnings[] = "{$relative} references CurrentSiteContext; verify it is not being used as a fallback source of truth.";
        }

        if ($isPageResolutionHotPath($relative) && preg_match('/\bSiteResolver\b/', $contents) === 1) {
            $errors[] = [
                'file' => $relative,
                'issue' => 'Page rendering/controller hot path references SiteResolver.',
                'suggestion' => 'Use the request-carried site context and already-bound site lookup service instead of re-resolving site context.',
            ];
        }
    }
}

if (!is_dir($docsReportDir) && !mkdir($docsReportDir, 0775, true) && !is_dir($docsReportDir)) {
    fwrite(STDERR, "Unable to create reports directory: {$docsReportDir}\n");
    exit(1);
}

$observations[] = "Scanned {$scannedFiles} PHP application file(s).";
$observations[] = 'This audit is read-only and does not modify application source.';
$observations[] = 'Concrete SiteRepository references outside approved areas are migration candidates, not hard failures.';

$report = [
    'phase' => '1.75-site-lookup-boundary-drift-guard-v2',
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
    foreach ($errors as $error) {
        echo "- {$error['file']}: {$error['issue']} {$error['suggestion']}\n";
    }
    echo "\n";
}

if ($migrationCandidates !== []) {
    echo "### Migration Candidates\n";
    foreach ($migrationCandidates as $candidate) {
        echo "- {$candidate['file']}: {$candidate['issue']} {$candidate['suggestion']}\n";
    }
    echo "\n";
}

if ($warnings !== []) {
    echo "### Warnings\n";
    foreach ($warnings as $warning) {
        echo "- {$warning}\n";
    }
    echo "\n";
}

if ($observations !== []) {
    echo "### Observations\n";
    foreach ($observations as $observation) {
        echo "- {$observation}\n";
    }
}

exit($errors === [] ? 0 : 1);
