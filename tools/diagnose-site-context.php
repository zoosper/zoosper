<?php

declare(strict_types=1);

/**
 * Diagnose website/store/store-view resolution for a host and path.
 *
 * This tool prints public site metadata only. It must never print credentials,
 * OTPs, TOTP secrets, recovery-code plaintext, reset tokens, SMTP passwords,
 * payment data, signed private URLs or customer-private values.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$options = getopt('', ['host::', 'path::']);
$host = (string) ($options['host'] ?? ($_SERVER['HTTP_HOST'] ?? ''));
$path = (string) ($options['path'] ?? '/');

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$resolver = (new \Zoosper\Core\Site\SiteContextResolverFactory($config))->create();
$context = $resolver->resolve($host, $path);
$cdn = (new \Zoosper\Core\Url\CdnUrlResolverFactory($config))->create();

print "Zoosper site context diagnostics\n";
print "================================\n\n";
print 'input_host       : ' . $host . PHP_EOL;
print 'input_path       : ' . $path . PHP_EOL;
foreach ($context->toArray() as $key => $value) {
    print str_pad($key, 17) . ': ' . $value . PHP_EOL;
}
print 'dynamic_url      : ' . $cdn->dynamicForContext('/example-page', $context) . PHP_EOL;
