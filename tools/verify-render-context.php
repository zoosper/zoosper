<?php

declare(strict_types=1);

/**
 * Verify render context classes and shared template context generation.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$options = getopt('', ['host::', 'path::']);
$host = isset($options['host']) ? strtolower(trim((string) $options['host'])) : (string) env('DEFAULT_SITE_HOST', 'localhost');
$path = isset($options['path']) ? trim((string) $options['path']) : '/';

$checks = [
    'Zoosper\\Core\\View\\TemplateViewContextProvider',
    'Zoosper\\Core\\Site\\SiteContextResolverFactory',
    'Zoosper\\Core\\Url\\CdnUrlResolverFactory',
    'Zoosper\\Core\\Cache\\CacheKeyBuilder',
    'Zoosper\\Theme\\Template\\TemplateRenderer',
    'Zoosper\\Page\\Service\\PageRenderer',
];

print "Zoosper render context verification\n";
print "===================================\n\n";

$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$siteResolver = (new \Zoosper\Core\Site\SiteContextResolverFactory($config))->create();
$siteContext = $siteResolver->resolve($host, $path);
$cdn = (new \Zoosper\Core\Url\CdnUrlResolverFactory($config))->create();
$cacheKeys = new \Zoosper\Core\Cache\CacheKeyBuilder();
$provider = new \Zoosper\Core\View\TemplateViewContextProvider($cdn, $cacheKeys);
$data = $provider->data('default', 'verify.render', $siteContext, $host, $path);

foreach (['siteContext', 'cdn', 'cacheContext', 'cacheKeys'] as $key) {
    $ok = array_key_exists($key, $data);
    print '- template_data.' . $key . ': ' . ($ok ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
