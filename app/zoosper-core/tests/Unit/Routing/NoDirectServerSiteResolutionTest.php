<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

/**
 * Phase 1.34g drift guard.
 *
 * Site resolution, render context and cache context must not grow direct
 * $_SERVER host/path reads in production source. Request::fromGlobals() is the
 * single approved boundary for host/path superglobal capture.
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

test('template and page render hot paths no longer depend on CurrentSiteContext fallback', function () {
    $root = dirname(__DIR__, 5);

    foreach ([
        'app/zoosper-core/src/View/TemplateViewContextProvider.php',
        'app/zoosper-page/src/Service/PageRenderer.php',
        'app/zoosper-core/config/services.php',
        'app/zoosper-page/config/services.php',
    ] as $relative) {
        $source = (string) file_get_contents($root . '/' . $relative);

        expect($source)->not->toContain('CurrentSiteContext');
    }
});
