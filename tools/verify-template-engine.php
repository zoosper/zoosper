<?php

declare(strict_types=1);

/**
 * Verify template engine adapter foundation.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$checks = [
    'Zoosper\\Theme\\Template\\Engine\\TemplateEngineInterface',
    'Zoosper\\Theme\\Template\\Engine\\PhpTemplateEngine',
    'Zoosper\\Theme\\Template\\Engine\\TemplateEngineRegistry',
    'Zoosper\\Theme\\Template\\TemplateRenderer',
];

print "Zoosper template engine verification\n";
print "====================================\n\n";
$failed = false;
foreach ($checks as $class) {
    $exists = class_exists($class) || interface_exists($class);
    print '- ' . $class . ': ' . ($exists ? 'ok' : 'missing') . PHP_EOL;
    $failed = $failed || !$exists;
}

$registry = new \Zoosper\Theme\Template\Engine\TemplateEngineRegistry(new \Zoosper\Theme\Template\Engine\PhpTemplateEngine());
$extensions = $registry->extensions();
print '- registered_extensions: ' . ($extensions === ['php'] ? 'ok' : 'check') . ' (' . implode(', ', $extensions) . ')' . PHP_EOL;
$failed = $failed || $extensions === [];

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
