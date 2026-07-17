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
$host = isset($options['host']) ? strtolower(trim((string) $options['host'])) : (string) env('DEFAULT_SITE_HOST', 'localhost');
$path = isset($options['path']) ? trim((string) $options['path']) : '/';
$theme = (string) ($options['theme'] ?? 'default');
$route = (string) ($options['route'] ?? 'frontend.page');

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$siteResolver = (new \Zoosper\Core\Site\SiteContextResolverFactory($config))->create();
$siteContext = $siteResolver->resolve($host, $path);
$cdn = (new \Zoosper\Core\Url\CdnUrlResolverFactory($config))->create();
$cacheKeys = new \Zoosper\Core\Cache\CacheKeyBuilder();
$provider = new \Zoosper\Core\View\TemplateViewContextProvider($cdn, $cacheKeys);
$data = $provider->data($theme, $route, $siteContext, $host, $path);

$cacheContext = $data['cacheContext'];

print "Zoosper render context diagnostics\n";
print "==================================\n\n";
print 'host             : ' . $host . PHP_EOL;
print 'path             : ' . $path . PHP_EOL;
print 'store_view       : ' . $siteContext->storeViewCode . PHP_EOL;
print 'locale           : ' . $siteContext->locale . PHP_EOL;
print 'currency         : ' . $siteContext->currency . PHP_EOL;
print 'dynamic_sample   : ' . $cdn->dynamicForContext('/diagnostic-page', $siteContext) . PHP_EOL;
print 'media_sample     : ' . $cdn->media('/library/example.jpg') . PHP_EOL;
print 'static_sample    : ' . $cdn->staticAsset('/admin/example.js') . PHP_EOL;
print 'page_cache_key   : ' . $cacheKeys->page($cacheContext, 'diagnostic_page') . PHP_EOL;
