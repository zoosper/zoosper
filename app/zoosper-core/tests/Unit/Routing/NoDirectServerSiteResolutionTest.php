<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

/**
 * Phase 1.34g drift guard.
 *
 * Site resolution, render context and cache context must not grow direct
 * $_SERVER host/path reads in production source. Request::fromGlobals() is the
 * single approved boundary for host/path superglobal capture. The former
 * CurrentSiteContext service-factory fallback was retired in Phase 1.34g.
 */
test('direct host and request-uri superglobal reads are restricted to the request boundary', function () {
    $root = dirname(__DIR__, 5);
    $appRoot = $root . '/app';

    $allowed = [
        'app/zoosper-core/src/Http/Request.php',
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

test('CurrentSiteContext service factory no longer reads host or request URI globals', function () {
    $root = dirname(__DIR__, 5);
    $services = (string) file_get_contents($root . '/app/zoosper-core/config/services.php');

    expect($services)->not->toContain('$_SERVER[\'HTTP_HOST\']');
    expect($services)->not->toContain('$_SERVER[\'REQUEST_URI\']');
    expect($services)->toContain('->default()');
});
