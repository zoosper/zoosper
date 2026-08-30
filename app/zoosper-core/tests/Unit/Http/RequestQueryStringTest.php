<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Http;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\SiteContext;

it('captures raw query string and provides accessor', function (): void {
    $request = new Request(
        method: 'GET',
        path: '/test',
        query: ['foo' => 'bar', 'page' => '2'],
        queryString: 'foo=bar&page=2',
    );

    expect($request->queryString())->toBe('foo=bar&page=2')
        ->and($request->query('foo'))->toBe('bar')
        ->and($request->query('page'))->toBe('2');

    $siteContext = new SiteContext(siteId: 1, host: 'localhost', isDefault: true);
    $withSite = $request->withSiteContext($siteContext);
    expect($withSite->queryString())->toBe('foo=bar&page=2');

    $withParams = $request->withRouteParams(['id' => '123']);
    expect($withParams->queryString())->toBe('foo=bar&page=2')
        ->and($withParams->routeParam('id'))->toBe('123');

    $withForm = $request->withForm(['csrf' => 'token']);
    expect($withForm->queryString())->toBe('foo=bar&page=2')
        ->and($withForm->form())->toBe(['csrf' => 'token']);
});
