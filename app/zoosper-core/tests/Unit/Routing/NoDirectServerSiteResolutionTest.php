<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

/**
 * Phase 1.34f drift guard.
 *
 * Site resolution, render context and cache context must not grow new direct
 * $_SERVER host/path reads in production source. The request boundary owns
 * superglobal reads; the only temporary exception is the legacy CurrentSiteContext
 * service factory in core/config/services.php, which is retired in Phase 1.34g
 * after all renderer callers are fully request-threaded.
 */
test('direct host and request-uri superglobal reads are restricted to approved production boundaries', function () {
    $root = dirname(__DIR__, 5);
    $appRoot = $root . '/app';

    $allowed = [
        'app/zoosper-core/src/Http/Request.php',
        'app/zoosper-core/src/Http/Application.php',
        'app/zoosper-core/config/services.php',
    ];

    $patterns = [
        '$_SERVER[\'HTTP_HOST\']',
        '$_SERVER["HTTP_HOST"]',
        '$_SERVER[\'REQUEST_URI\']',
        '$_SERVER["REQUEST_URI"]',
    ];

    $violations = [];
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($appRoot, \FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        /** @var \SplFileInfo $file */
        if (!$file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }

        $relative = ltrim(str_replace($root, '', $file->getPathname()), '/\\');

        // Tests deliberately contain $_SERVER fixtures and string patterns to
        // prove globals are ignored. The guard is for production source only.
        if (str_contains($relative, '/tests/')) {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());

        foreach ($patterns as $pattern) {
            if (str_contains($source, $pattern) && !in_array($relative, $allowed, true)) {
                $violations[] = $relative . ' contains ' . $pattern;
            }
        }
    }

    sort($violations);

    expect($violations)->toBe([]);
});

test('the temporary CurrentSiteContext factory remains the only approved legacy host/path fallback', function () {
    $root = dirname(__DIR__, 5);
    $services = (string) file_get_contents($root . '/app/zoosper-core/config/services.php');

    expect(substr_count($services, '$_SERVER[\'HTTP_HOST\']'))->toBeLessThanOrEqual(1);
    expect(substr_count($services, '$_SERVER[\'REQUEST_URI\']'))->toBeLessThanOrEqual(1);
});