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
    'Zoosper\\Core\\Plugin\\MethodPluginFactory',
    'Zoosper\\Core\\Plugin\\MethodPluginExecutor',
    'Zoosper\\Core\\Plugin\\MethodPluginFileConfigLoader',
];

$errors = 0;
$report = [];
$report[] = '## Method Plugin Discovery Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($requiredClasses as $class) {
    $exists = class_exists($class);
    $report[] = '- ' . $class . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Runtime paths intercepted: no';
$report[] = 'Safe sample proof tool exists: ' . (is_file($root . '/tools/prove-method-plugin-sample-service.php') ? 'yes' : 'no');
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/method-plugin-discovery.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-discovery.log', "METHOD_PLUGIN_DISCOVERY_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
