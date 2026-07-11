<?php

declare(strict_types=1);

/**
 * Diagnose shared render context data for templates.
 *
 * Output is intentionally limited to public site/cache metadata. It must never
 * print credentials, session IDs, CSRF tokens, OTPs, TOTP secrets,
 * recovery-code plaintext, reset tokens, SMTP passwords, payment data or
 * customer-private values.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$options = getopt('', ['host::', 'path::', 'theme::', 'route::']);

if (isset($options['host'])) {
    $_SERVER['HTTP_HOST'] = (string) $options['host'];
}
if (isset($options['path'])) {
    $_SERVER['REQUEST_URI'] = (string) $options['path'];
}

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$siteResolver = (new \Zoosper\Core\Site\SiteContextResolverFactory($config))->create();
$currentSite = new \Zoosper\Core\Site\CurrentSiteContext($siteResolver);
$cdn = (new \Zoosper\Core\Url\CdnUrlResolverFactory($config))->create();
$cacheKeys = new \Zoosper\Core\Cache\CacheKeyBuilder();
$provider = new \Zoosper\Core\View\TemplateViewContextProvider($currentSite, $cdn, $cacheKeys);
$data = $provider->data((string) ($options['theme'] ?? 'default'), (string) ($options['route'] ?? 'frontend.page'));

$siteContext = $data['siteContext'];
$cacheContext = $data['cacheContext'];

print "Zoosper render context diagnostics\n";
print "==================================\n\n";
print 'store_view       : ' . $siteContext->storeViewCode . PHP_EOL;
print 'locale           : ' . $siteContext->locale . PHP_EOL;
print 'currency         : ' . $siteContext->currency . PHP_EOL;
print 'dynamic_sample   : ' . $cdn->dynamicForContext('/diagnostic-page', $siteContext) . PHP_EOL;
print 'media_sample     : ' . $cdn->media('/library/example.jpg') . PHP_EOL;
print 'static_sample    : ' . $cdn->staticAsset('/admin/example.js') . PHP_EOL;
print 'page_cache_key   : ' . $cacheKeys->page($cacheContext, 'diagnostic_page') . PHP_EOL;
