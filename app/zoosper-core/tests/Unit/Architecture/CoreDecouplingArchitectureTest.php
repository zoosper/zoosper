<?php

declare(strict_types=1);

/*
 * Phase 1.99 behavioural architecture guard.
 *
 * This is a REAL test (not a file-existence assertion): it scans every PHP file
 * under app/zoosper-core/src and fails if core references any feature-module
 * namespace. It executes the rule both reviewers asked for - a namespace-ban
 * architecture guard - so core->feature coupling can never silently return.
 *
 * Repo root is five levels up from app/zoosper-core/tests/Unit/Architecture.
 */

function coreSourceDir(): string
{
    return dirname(__DIR__, 3) . '/src';
}

/**
 * Feature-module namespaces that core must never depend on. Core may only depend
 * on itself (Zoosper\Core\...) and PHP/vendor. This mirrors the allowed seam:
 * feature modules depend on core interfaces, never the reverse.
 *
 * @return list<string>
 */
function forbiddenFeatureNamespaces(): array
{
    return [
        'Zoosper\Page\',
        'Zoosper\Site\',
        'Zoosper\Auth\',
        'Zoosper\Theme\',
        'Zoosper\Media\',
        'Zoosper\Admin\',
        'Zoosper\Api\',
        'Zoosper\Mail\',
        'Zoosper\TwoFactor\',
        'Zoosper\UrlRewrite\',
    ];
}

/**
 * @return list<string> Absolute paths to every PHP file under core src.
 */
function coreSourceFiles(): array
{
    $dir = coreSourceDir();
    if (!is_dir($dir)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

it('has core source files to scan', function (): void {
    expect(coreSourceFiles())->not->toBe([]);
});

it('keeps app/zoosper-core/src free of feature-module namespaces', function (): void {
    $root = dirname(__DIR__, 5);
    $forbidden = forbiddenFeatureNamespaces();
    $violations = [];

    foreach (coreSourceFiles() as $file) {
        $source = (string) file_get_contents($file);
        $relative = str_replace($root . '/', '', $file);

        foreach ($forbidden as $namespace) {
            if (str_contains($source, $namespace)) {
                $violations[] = $relative . ' references ' . $namespace;
            }
        }
    }

    expect($violations)->toBe(
        [],
        "Core must not depend on feature modules. Violations:\n- " . implode("\n- ", $violations)
    );
});

it('does not import feature controllers or repositories by class-use in core', function (): void {
    $root = dirname(__DIR__, 5);
    $violations = [];

    // A stricter check: any `use Zoosper\<Feature>\...;` import statement in core.
    $pattern = '/^\s*use\s+Zoosper\(Page|Site|Auth|Theme|Media|Admin|Api|Mail|TwoFactor|UrlRewrite)\/m';

    foreach (coreSourceFiles() as $file) {
        $source = (string) file_get_contents($file);
        if (preg_match($pattern, $source) === 1) {
            $violations[] = str_replace($root . '/', '', $file);
        }
    }

    expect($violations)->toBe(
        [],
        "Core files must not `use` feature-module classes. Offending files:\n- " . implode("\n- ", $violations)
    );
});










