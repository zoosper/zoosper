<?php

declare(strict_types=1);

/**
 * Diagnose frontend dynamic/static/media URLs from the current CDN and site context configuration.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$options = getopt('', ['host::', 'path::', 'theme::']);
$theme = isset($options['theme']) ? trim((string) $options['theme']) : 'default';

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
$siteContext = $currentSite->get();
$staticPath = '/themes/' . $theme . '/assets/css/app.css';
$publicFile = $basePath . '/public/static' . $staticPath;

print "Zoosper frontend CDN URL diagnostics\n";
print "====================================\n\n";
print 'store_view             : ' . $siteContext->storeViewCode . PHP_EOL;
print 'locale                 : ' . $siteContext->locale . PHP_EOL;
print 'dynamic_home           : ' . $cdn->dynamicForContext('/', $siteContext) . PHP_EOL;
print 'dynamic_about          : ' . $cdn->dynamicForContext('/about-us', $siteContext) . PHP_EOL;
print 'static_css_url         : ' . $cdn->staticAsset($staticPath) . PHP_EOL;
print 'static_css_public_file : ' . $publicFile . PHP_EOL;
print 'static_css_file_exists : ' . (is_file($publicFile) ? 'yes' : 'no') . PHP_EOL;
print 'media_example          : ' . $cdn->media('/library/example.jpg') . PHP_EOL;
