<?php

declare(strict_types=1);

/**
 * Diagnose cache context and safe key generation for a host/path.
 *
 * This tool prints only public routing/cache metadata. It must never print
 * credentials, session IDs, CSRF tokens, OTPs, TOTP secrets, recovery-code
 * plaintext, reset tokens, SMTP passwords, payment data or customer-private
 * values.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$options = getopt('', ['host::', 'path::', 'theme::', 'route::', 'auth::']);
$host = (string) ($options['host'] ?? ($_SERVER['HTTP_HOST'] ?? ''));
$path = (string) ($options['path'] ?? '/');
$theme = (string) ($options['theme'] ?? 'default');
$route = (string) ($options['route'] ?? 'frontend.page');
$isAuthenticated = filter_var((string) ($options['auth'] ?? 'false'), FILTER_VALIDATE_BOOLEAN);

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$siteContext = (new \Zoosper\Core\Site\SiteContextResolverFactory($config))->create()->resolve($host, $path);
$cacheContext = \Zoosper\Core\Cache\CacheContext::fromSiteContext($siteContext, $host, $path, $theme, $isAuthenticated, $isAuthenticated ? 'authenticated' : 'guest', $route);
$keyBuilder = new \Zoosper\Core\Cache\CacheKeyBuilder();

print "Zoosper cache context diagnostics\n";
print "=================================\n\n";
foreach ($cacheContext->toArray() as $key => $value) {
    print str_pad($key, 17) . ': ' . $value . PHP_EOL;
}

print "\nCache keys:\n";
print 'page                 : ' . $keyBuilder->page($cacheContext, 'cms_page') . PHP_EOL;
print 'menu_block           : ' . $keyBuilder->block($cacheContext, 'main_menu') . PHP_EOL;
print 'public_fragment      : ' . $keyBuilder->publicFragment($cacheContext, 'announcement_bar') . PHP_EOL;
print 'private_fragment     : ' . $keyBuilder->privateFragment($cacheContext, 'customer_header_state') . PHP_EOL;

print "\nHTTP policies:\n";
foreach ([
    \Zoosper\Core\Cache\HttpCachePolicy::publicPage(),
    \Zoosper\Core\Cache\HttpCachePolicy::publicFragment(),
    \Zoosper\Core\Cache\HttpCachePolicy::privateFragment(),
    \Zoosper\Core\Cache\HttpCachePolicy::noStore(),
] as $policy) {
    print '- ' . $policy->code . ': ' . ($policy->headers['Cache-Control'] ?? '') . PHP_EOL;
}
