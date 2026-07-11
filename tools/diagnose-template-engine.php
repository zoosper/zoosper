<?php

declare(strict_types=1);

/**
 * Diagnose configured template engine foundation.
 */

$basePath = require __DIR__ . '/bootstrap.php';
$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$templateConfig = $config->array('template');
$registry = new \Zoosper\Theme\Template\Engine\TemplateEngineRegistry(new \Zoosper\Theme\Template\Engine\PhpTemplateEngine());

print "Zoosper template engine diagnostics\n";
print "===================================\n\n";
print 'default_engine           : ' . (string) ($templateConfig['default_engine'] ?? 'php') . PHP_EOL;
print 'preferred_modern_engine  : ' . (string) ($templateConfig['preferred_modern_engine'] ?? 'latte') . PHP_EOL;
print 'allow_custom_engines     : ' . (((bool) ($templateConfig['allow_custom_engines'] ?? true)) ? 'yes' : 'no') . PHP_EOL;
print 'registered_extensions    : ' . implode(', ', $registry->extensions()) . PHP_EOL;
print 'next_recommended_engine  : latte' . PHP_EOL;
