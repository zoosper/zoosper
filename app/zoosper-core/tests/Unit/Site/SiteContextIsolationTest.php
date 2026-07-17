<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Site;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\SiteContextResolver;

/**
 * Phase 1.34 proof-of-isolation tests.
 *
 * These assert that the resolved site context is carried immutably per request
 * and cannot bleed between requests. Phase 1.34g-b removes the legacy container
 * holder entirely, so Request::siteContext() is the only supported per-request
 * carrier in the application runtime.
 */

/**
 * A resolver with two distinct host-mapped store views for isolation testing.
 */
function isolationResolver(): SiteContextResolver
{
    return new SiteContextResolver([
        'default_store_view' => 'default',
        'store_views' => [
            'default' => [
                'website_code' => 'main',
                'website_name' => 'Main Website',
                'store_code' => 'main',
                'store_name' => 'Main Store',
                'store_view_code' => 'default',
                'store_view_name' => 'Default',
                'locale' => 'en_AU',
                'currency' => 'AUD',
                'base_url' => 'https://main.example',
                'domains' => ['main.example'],
                'is_active' => true,
            ],
            'other' => [
                'website_code' => 'other',
                'website_name' => 'Other Website',
                'store_code' => 'other',
                'store_name' => 'Other Store',
                'store_view_code' => 'other',
                'store_view_name' => 'Other',
                'locale' => 'en_NZ',
                'currency' => 'NZD',
                'base_url' => 'https://other.example',
                'domains' => ['other.example'],
                'is_active' => true,
            ],
        ],
    ]);
}

test('two requests with different hosts carry independent site contexts', function () {
    $resolver = isolationResolver();

    $requestA = (new Request('GET', '/'))->withSiteContext($resolver->resolve('main.example', '/'));
    $requestB = (new Request('GET', '/'))->withSiteContext($resolver->resolve('other.example', '/'));

    expect($requestA->siteContext())->not->toBeNull();
    expect($requestB->siteContext())->not->toBeNull();
    expect($requestA->siteContext()->websiteCode)->toBe('main');
    expect($requestB->siteContext()->websiteCode)->toBe('other');

    // Resolving the second request did not mutate the first (no shared state).
    expect($requestA->siteContext()->websiteCode)->toBe('main');
    expect($requestA->siteContext()->currency)->toBe('AUD');
    expect($requestB->siteContext()->currency)->toBe('NZD');
});

test('withSiteContext returns a new immutable request without mutating the original', function () {
    $resolver = isolationResolver();
    $base = new Request('GET', '/');
    $withContext = $base->withSiteContext($resolver->resolve('main.example', '/'));

    expect($withContext)->not->toBe($base);
    expect($base->siteContext())->toBeNull();
    expect($withContext->siteContext())->not->toBeNull();
});

test('legacy current site context holder file is retired', function () {
    $root = dirname(__DIR__, 5);

    expect(is_file($root . '/app/zoosper-core/src/Site/CurrentSiteContext.php'))->toBeFalse();
});
