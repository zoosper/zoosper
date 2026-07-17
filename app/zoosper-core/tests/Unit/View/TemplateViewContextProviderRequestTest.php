<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\View;

use Zoosper\Core\Cache\CacheKeyBuilder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\CurrentSiteContext;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Core\Url\CdnUrlResolver;
use Zoosper\Core\View\TemplateViewContextProvider;
use Zoosper\Theme\Template\Engine\TemplateEngineInterface;
use Zoosper\Theme\Template\Engine\TemplateEngineRegistry;
use Zoosper\Theme\Template\TemplateRenderer;
use Zoosper\Theme\Theme\ThemeResolver;

function viewSiteContext(string $code, string $baseUrl = 'https://example.test'): SiteContext
{
    return new SiteContext(
        websiteCode: $code,
        websiteName: strtoupper($code),
        storeCode: $code,
        storeName: strtoupper($code),
        storeViewCode: $code . '_view',
        storeViewName: strtoupper($code) . ' View',
        locale: 'en_AU',
        currency: 'AUD',
        baseUrl: $baseUrl,
        pathPrefix: '',
    );
}

function providerWithFallback(SiteContext $fallback): TemplateViewContextProvider
{
    return new TemplateViewContextProvider(
        new CurrentSiteContext($fallback),
        new CdnUrlResolver([]),
        new CacheKeyBuilder(),
    );
}

test('template view context uses explicit request values instead of globals', function () {
    $_SERVER['HTTP_HOST'] = 'wrong.example';
    $_SERVER['REQUEST_URI'] = '/wrong-path';

    $provider = providerWithFallback(viewSiteContext('fallback', 'https://fallback.example'));
    $explicit = viewSiteContext('site_a', 'https://site-a.example');

    $data = $provider->data(
        themeCode: 'aurora',
        routeName: 'page.view',
        siteContext: $explicit,
        host: 'Site-A.example:8443',
        path: '/products/view/',
    );

    expect($data['siteContext'])->toBe($explicit);
    expect($data['cacheContext']->websiteCode)->toBe('site_a');
    expect($data['cacheContext']->host)->toBe('site-a.example');
    expect($data['cacheContext']->path)->toBe('/products/view');
    expect($data['cacheContext']->themeCode)->toBe('aurora');
    expect($data['cacheContext']->routeName)->toBe('page.view');
});

test('template view context falls back to immutable CurrentSiteContext without reading globals', function () {
    $_SERVER['HTTP_HOST'] = 'wrong.example';
    $_SERVER['REQUEST_URI'] = '/wrong-path';

    $fallback = viewSiteContext('fallback', 'https://fallback.example');
    $data = providerWithFallback($fallback)->data(themeCode: 'default', routeName: 'fallback.route');

    expect($data['siteContext'])->toBe($fallback);
    expect($data['cacheContext']->websiteCode)->toBe('fallback');
    expect($data['cacheContext']->host)->toBe('fallback.example');
    expect($data['cacheContext']->path)->toBe('/');
});

test('template renderer threads request context into shared template data', function () {
    $root = sys_get_temp_dir() . '/zoosper-template-thread-' . bin2hex(random_bytes(4));
    $templateDir = $root . '/default/templates';
    mkdir($templateDir, 0775, true);
    file_put_contents($templateDir . '/view.test', 'placeholder');

    $engine = new class implements TemplateEngineInterface {
        public function extensions(): array
        {
            return ['test'];
        }

        public function renderFile(string $path, array $data): string
        {
            return implode('|', [
                $data['siteContext']->websiteCode,
                $data['cacheContext']->host,
                $data['cacheContext']->path,
                $data['cacheContext']->themeCode,
                $data['cacheContext']->routeName,
            ]);
        }
    };

    $provider = providerWithFallback(viewSiteContext('fallback', 'https://fallback.example'));
    $renderer = new TemplateRenderer(
        new ThemeResolver($root, 'default'),
        null,
        null,
        $provider,
        new TemplateEngineRegistry($engine),
    );

    $request = (new Request('GET', '/catalog/page', host: 'site-a.example'))
        ->withSiteContext(viewSiteContext('site_a', 'https://site-a.example'));

    $html = $renderer->render('view', [], 'default', 'page.view', $request);

    expect($html)->toBe('site_a|site-a.example|/catalog/page|default|page.view');
});
