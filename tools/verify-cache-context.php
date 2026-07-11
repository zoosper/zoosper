<?php

declare(strict_types=1);

/**
 * Verify cache context, cache key and AJAX fragment foundation classes.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$checks = [
    'Zoosper\\Core\\Cache\\CacheContext',
    'Zoosper\\Core\\Cache\\CacheKeyBuilder',
    'Zoosper\\Core\\Cache\\HttpCachePolicy',
    'Zoosper\\Core\\Fragment\\AjaxFragmentDefinition',
    'Zoosper\\Core\\Fragment\\FragmentResponseMetadata',
    'Zoosper\\Core\\Site\\SiteContextResolverFactory',
];

print "Zoosper cache context verification\n";
print "=================================\n\n";

$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$siteContext = (new \Zoosper\Core\Site\SiteContextResolverFactory($config))->create()->default();
$cacheContext = \Zoosper\Core\Cache\CacheContext::fromSiteContext($siteContext, 'example.test', '/verify', 'default', false, 'guest', 'verify.route');
$key = (new \Zoosper\Core\Cache\CacheKeyBuilder())->page($cacheContext, 'verify');
$policy = \Zoosper\Core\Cache\HttpCachePolicy::publicPage();

print '- page_cache_key: ' . (str_contains($key, 'store_view=') ? 'ok' : 'invalid') . ' (' . $key . ')' . PHP_EOL;
print '- public_policy: ' . (isset($policy->headers['Cache-Control']) ? 'ok' : 'invalid') . PHP_EOL;
$failed = $failed || !str_contains($key, 'store_view=') || !isset($policy->headers['Cache-Control']);

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
