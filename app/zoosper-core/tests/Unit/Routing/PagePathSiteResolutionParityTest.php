<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

/**
 * Phase 1.34e drift guard: the frontend/API page hot paths must not re-resolve
 * site context through the legacy Zoosper\Site\Service\SiteResolver. They should
 * use Request::siteContext() and the numeric siteId carried there.
 */

function projectPath(string $relative): string
{
    return dirname(__DIR__, 5) . '/' . ltrim($relative, '/');
}

test('page hot path controllers no longer depend on legacy SiteResolver', function () {
    foreach ([
        'app/zoosper-page/src/Controller/PageController.php',
        'app/zoosper-api/src/Controller/ContentPageController.php',
    ] as $file) {
        $source = (string) file_get_contents(projectPath($file));

        expect($source)->not->toContain('Zoosper\\Site\\Service\\SiteResolver');
        expect($source)->not->toContain('private SiteResolver');
        expect($source)->not->toContain('siteResolver');
        expect($source)->not->toContain('->resolve($request->host())');
        expect($source)->toContain('siteContext()');
    }
});

test('page hot path factories no longer inject legacy SiteResolver', function () {
    foreach ([
        'app/zoosper-page/config/services.php',
        'app/zoosper-api/config/controllers.php',
    ] as $file) {
        $source = (string) file_get_contents(projectPath($file));

        expect($source)->not->toContain('Zoosper\\Site\\Service\\SiteResolver');
        expect($source)->not->toContain('SiteResolver::class');
    }
});

test('application factory loads root service providers only once', function () {
    $source = (string) file_get_contents(projectPath('app/zoosper-core/src/Bootstrap/ApplicationFactory.php'));

    expect(substr_count($source, 'ServiceProviderManifestLoader'))->toBe(1);
});
