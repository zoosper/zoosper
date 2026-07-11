<?php

declare(strict_types=1);

/**
 * Verify the CDN URL resolver classes and a few safe URL examples.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$checks = [
    'Zoosper\\Core\\Url\\CdnUrlType',
    'Zoosper\\Core\\Url\\CdnUrlResolver',
    'Zoosper\\Core\\Url\\CdnUrlResolverFactory',
];

print "Zoosper CDN URL resolver verification\n";
print "====================================\n\n";

$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$resolver = (new \Zoosper\Core\Url\CdnUrlResolverFactory($config))->create();

$examples = [
    'dynamic' => $resolver->dynamic('/demo-page', 'default'),
    'media' => $resolver->media('library/example.jpg'),
    'static' => $resolver->staticAsset('admin/example.js'),
];

foreach ($examples as $type => $url) {
    $ok = $url !== '' && str_contains($url, '/');
    print '- sample_' . $type . ': ' . ($ok ? 'ok' : 'invalid') . ' (' . $url . ')' . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
