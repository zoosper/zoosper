<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Http;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\SiteContext;

test('withRouteParams returns a new immutable request without mutating the original', function () {
    $base = new Request('GET', '/admin/pages/edit');
    $withParams = $base->withRouteParams(['id' => '123']);

    expect($withParams)->not->toBe($base);
    expect($base->routeParam('id'))->toBeNull();
    expect($base->routeParams())->toBe([]);
    expect($withParams->routeParam('id'))->toBe('123');
    expect($withParams->routeParams())->toBe(['id' => '123']);
});

test('routeParam returns the provided default for a missing parameter', function () {
    $request = (new Request('GET', '/admin/pages/edit'))->withRouteParams(['id' => '123']);

    expect($request->routeParam('missing', 'fallback'))->toBe('fallback');
});

test('withRouteParams preserves existing request metadata and site context', function () {
    $siteContext = new SiteContext(
        websiteCode: 'main',
        websiteName: 'Main Website',
        storeCode: 'main',
        storeName: 'Main Store',
        storeViewCode: 'default',
        storeViewName: 'Default Store View',
        locale: 'en_AU',
        currency: 'AUD',
        baseUrl: 'https://example.test',
        siteId: 7,
    );

    $request = (new Request('GET', '/admin/pages/edit', host: 'Example.test:8443'))
        ->withSiteContext($siteContext)
        ->withRouteParams(['id' => 42]);

    expect($request->method())->toBe('GET');
    expect($request->host())->toBe('Example.test');
    expect($request->siteContext())->toBe($siteContext);
    expect($request->routeParam('id'))->toBe('42');
});
