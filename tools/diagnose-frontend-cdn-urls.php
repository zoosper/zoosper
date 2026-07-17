<?php

declare(strict_types=1);

/**
 * Diagnose frontend dynamic/static/media URLs from the CDN and explicit site context configuration.
 *
 * Output is limited to public site/CDN metadata. It must never print secrets,
 * OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords,
 * payment data or customer-private values.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$options = getopt('', ['host::', 'path::', 'theme::']);
$theme = isset($options['theme']) ? trim((string) $options['theme']) : 'default';
$host = isset($options['host']) ? strtolower(trim((string) $options['host'])) : (string) env('DEFAULT_SITE_HOST', 'localhost');
$path = isset($options['path']) ? trim((string) $options['path']) : '/';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$siteResolver = (new \Zoosper\Core\Site\SiteContextResolverFactory($config))->create();
$siteContext = $siteResolver->resolve($host, $path);
$cdn = (new \Zoosper\Core\Url\CdnUrlResolverFactory($config))->create();
$staticPath = '/themes/' . $theme . '/assets/css/app.css';
$publicFile = $basePath . '/public/static' . $staticPath;

print "Zoosper frontend CDN URL diagnostics\n";
print "====================================\n\n";
print 'host                   : ' . $host . PHP_EOL;
print 'path                   : ' . $path . PHP_EOL;
print 'store_view             : ' . $siteContext->storeViewCode . PHP_EOL;
print 'locale                 : ' . $siteContext->locale . PHP_EOL;
print 'dynamic_home           : ' . $cdn->dynamicForContext('/', $siteContext) . PHP_EOL;
print 'dynamic_about          : ' . $cdn->dynamicForContext('/about-us', $siteContext) . PHP_EOL;
print 'static_css_url         : ' . $cdn->staticAsset($staticPath) . PHP_EOL;
print 'static_css_public_file : ' . $publicFile . PHP_EOL;
print 'static_css_file_exists : ' . (is_file($publicFile) ? 'yes' : 'no') . PHP_EOL;
print 'media_example          : ' . $cdn->media('/library/example.jpg') . PHP_EOL;
