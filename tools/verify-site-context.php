<?php

declare(strict_types=1);

/**
 * Verify site context classes and resolution without relying on hard-coded store code usage.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$checks = [
    'Zoosper\\Core\\Site\\SiteContext',
    'Zoosper\\Core\\Site\\SiteContextResolver',
    'Zoosper\\Core\\Site\\CurrentSiteContext',
    'Zoosper\\Core\\Site\\SiteContextResolverFactory',
    'Zoosper\\Core\\Url\\CdnUrlResolver',
];

print "Zoosper site context verification\n";
print "=================================\n\n";

$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$resolver = (new \Zoosper\Core\Site\SiteContextResolverFactory($config))->create();
$context = $resolver->resolve('example.test', '/');
$cdn = (new \Zoosper\Core\Url\CdnUrlResolverFactory($config))->create();
$dynamic = $cdn->dynamicForContext('/verify-page', $context);

print '- resolved_store_view: ' . ($context->storeViewCode !== '' ? 'ok' : 'invalid') . ' (' . $context->storeViewCode . ')' . PHP_EOL;
print '- context_dynamic_url: ' . ($dynamic !== '' ? 'ok' : 'invalid') . ' (' . $dynamic . ')' . PHP_EOL;
$failed = $failed || $context->storeViewCode === '' || $dynamic === '';

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
