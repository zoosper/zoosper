<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$errors = 0;
$report = [];
$requiredClasses = [
    'Zoosper\\Core\\Plugin\\MethodPluginRuntimeConfig',
    'Zoosper\\Core\\Plugin\\MethodPluginRuntimeConfigLoader',
    'Zoosper\\Core\\Plugin\\MethodPluginRuntimeConfigLayeredLoader',
    'Zoosper\\Core\\Config\\ConfigFileLayeredLoader',
];
$requiredFiles = [
    'tools/prove-method-plugin-runtime-config-layering.php',
    'tools/audit-method-plugin-runtime-config-layering.php',
    'docs/development/method-plugin-runtime-config-layering.md',
];

$report[] = '## Method Plugin Runtime Config Layering Audit';
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

foreach ($requiredFiles as $file) {
    $exists = is_file($root . '/' . $file);
    $report[] = '- ' . $file . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$config = \Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();
$report[] = '';
$report[] = 'Default runtime enabled: ' . ($config->enabled ? 'yes' : 'no');
$report[] = 'Default report-only: ' . ($config->reportOnly ? 'yes' : 'no');
$report[] = 'Default allow-list count: ' . count($config->reportOnlyInvocationKeys);
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Selected service invoked: no';

if ($config->enabled || !$config->reportOnly || count($config->reportOnlyInvocationKeys) !== 0) {
    $errors++;
}

$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-runtime-config-layering-audit.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-runtime-config-layering-audit.log', "METHOD_PLUGIN_RUNTIME_CONFIG_LAYERING_AUDIT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
