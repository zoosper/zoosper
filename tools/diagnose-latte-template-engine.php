<?php

declare(strict_types=1);

/**
 * Diagnose Latte template engine setup.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$templateConfig = $config->array('template');
$cachePath = $basePath . '/' . ltrim((string) ($templateConfig['template_cache_path'] ?? 'var/cache/templates'), '/');

print "Zoosper Latte template engine diagnostics\n";
print "=========================================\n\n";
print 'Latte\\Engine available : ' . (class_exists(\Latte\Engine::class) ? 'yes' : 'no') . PHP_EOL;
print 'default_engine          : ' . (string) ($templateConfig['default_engine'] ?? 'php') . PHP_EOL;
print 'preferred_engine        : ' . (string) ($templateConfig['preferred_modern_engine'] ?? 'latte') . PHP_EOL;
print 'cache_path              : ' . $cachePath . PHP_EOL;
print 'cache_path_exists       : ' . (is_dir($cachePath) ? 'yes' : 'no') . PHP_EOL;
print 'sample_template         : ' . $basePath . '/themes/default/templates/examples/hello.latte' . PHP_EOL;
