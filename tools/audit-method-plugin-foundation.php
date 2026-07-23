<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$requiredClasses = [
    'Zoosper\\Core\\Plugin\\MethodInvocation',
    'Zoosper\\Core\\Plugin\\MethodInterceptorInterface',
    'Zoosper\\Core\\Plugin\\CallableMethodInterceptor',
    'Zoosper\\Core\\Plugin\\MethodInterceptorChain',
    'Zoosper\\Core\\Plugin\\MethodPluginDefinition',
    'Zoosper\\Core\\Plugin\\MethodPluginRegistry',
    'Zoosper\\Core\\Plugin\\MethodPluginConfigLoader',
];

$errors = 0;
$report = [];
$report[] = '## Method Plugin Foundation Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredClasses as $class) {
    $exists = class_exists($class) || interface_exists($class);
    $report[] = '- ' . $class . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/method-plugin-foundation.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-foundation.log', "METHOD_PLUGIN_FOUNDATION_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
