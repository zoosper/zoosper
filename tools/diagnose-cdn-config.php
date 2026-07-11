<?php

declare(strict_types=1);

/**
 * Print safe CDN URL configuration diagnostics.
 *
 * This tool never prints credentials, signed secrets, OTPs, TOTP secrets,
 * recovery-code plaintext, reset tokens, payment data or customer-private
 * values. CDN base URLs should be public infrastructure values only.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$resolver = (new \Zoosper\Core\Url\CdnUrlResolverFactory($config))->create();
$cdn = $config->array('cdn');

print "Zoosper CDN configuration diagnostics\n";
print "=====================================\n\n";
print 'enabled               : ' . ($resolver->isEnabled() ? 'yes' : 'no') . PHP_EOL;
print 'dynamic_base_url      : ' . (string) ($cdn['dynamic']['base_url'] ?? '') . PHP_EOL;
print 'media_base_url        : ' . (string) ($cdn['media']['base_url'] ?? '') . PHP_EOL;
print 'media_path_prefix     : ' . (string) ($cdn['media']['path_prefix'] ?? '') . PHP_EOL;
print 'static_base_url       : ' . (string) ($cdn['static']['base_url'] ?? '') . PHP_EOL;
print 'static_path_prefix    : ' . (string) ($cdn['static']['path_prefix'] ?? '') . PHP_EOL;
print 'sample_dynamic        : ' . $resolver->dynamic('/about-us', 'default') . PHP_EOL;
print 'sample_media          : ' . $resolver->media('/catalog/image.jpg') . PHP_EOL;
print 'sample_static         : ' . $resolver->staticAsset('/admin/app.css') . PHP_EOL;

$storeUrls = $cdn['dynamic']['store_base_urls'] ?? [];
if (is_array($storeUrls) && $storeUrls !== []) {
    print "\nStore dynamic base URLs:\n";
    foreach ($storeUrls as $storeCode => $url) {
        print '- ' . (string) $storeCode . ': ' . (string) $url . PHP_EOL;
    }
}
