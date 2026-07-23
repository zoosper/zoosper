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
    'Zoosper\\Core\\Plugin\\MethodPluginConfigSource',
    'Zoosper\\Core\\Plugin\\MethodPluginConfigSourceDiscovery',
    'Zoosper\\Core\\Plugin\\MethodPluginModuleConfigLoader',
];

$errors = 0;
$report = [];
$report[] = '## Method Plugin Module Discovery Audit';
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
$report[] = 'Module discovery proof tool exists: ' . (is_file($root . '/tools/prove-method-plugin-module-discovery.php') ? 'yes' : 'no');
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/method-plugin-module-discovery.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-module-discovery.log', "METHOD_PLUGIN_MODULE_DISCOVERY_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
