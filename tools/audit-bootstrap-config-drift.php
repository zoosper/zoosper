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
$warnings = 0;
$report = [];

$report[] = '## Bootstrap Config Drift Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$adminMiddlewareFiles = array_merge(
    glob($root . '/app/*/config/admin_middleware.php') ?: [],
    glob($root . '/packages/*/config/admin_middleware.php') ?: [],
    glob($root . '/packages/*/*/config/admin_middleware.php') ?: []
);

$report[] = '### Admin middleware config files';
$report[] = 'Files scanned: ' . count($adminMiddlewareFiles);
foreach ($adminMiddlewareFiles as $file) {
    $relative = str_replace($root . '/', '', $file);
    $report[] = '- ' . $relative;

    try {
        $config = require $file;
    } catch (Throwable $exception) {
        $report[] = '  ERROR load failed: ' . $exception->getMessage();
        $errors++;
        continue;
    }

    if (!is_array($config)) {
        $report[] = '  ERROR config must return array, got ' . get_debug_type($config);
        $errors++;
        continue;
    }

    $entries = $config;
    if (isset($entries['admin']) && is_array($entries['admin'])) {
        $entries = $entries['admin'];
        $warnings++;
        $report[] = '  WARNING wrapper key admin detected';
    } elseif (isset($entries['middleware']) && is_array($entries['middleware'])) {
        $entries = $entries['middleware'];
        $warnings++;
        $report[] = '  WARNING wrapper key middleware detected';
    }

    foreach (array_values($entries) as $index => $entry) {
        if ($entry instanceof Closure) {
            $report[] = '  ERROR entry ' . $index . ' is Closure';
            $errors++;
            continue;
        }
        if (!is_string($entry) || $entry === '') {
            $report[] = '  ERROR entry ' . $index . ' must be non-empty class string, got ' . get_debug_type($entry);
            $errors++;
            continue;
        }
        if (!class_exists($entry)) {
            $report[] = '  WARNING middleware class not autoloadable: ' . $entry;
            $warnings++;
        }
    }
}

$pluginRuntimeConfigFiles = array_merge(
    glob($root . '/app/*/config/method_plugins.php') ?: [],
    glob($root . '/app/*/config/method_plugin_runtime.php') ?: [],
    glob($root . '/packages/*/config/method_plugins.php') ?: [],
    glob($root . '/packages/*/config/method_plugin_runtime.php') ?: [],
    glob($root . '/packages/*/*/config/method_plugins.php') ?: [],
    glob($root . '/packages/*/*/config/method_plugin_runtime.php') ?: []
);

$report[] = '';
$report[] = '### Method plugin runtime config files';
$report[] = 'Files scanned: ' . count($pluginRuntimeConfigFiles);
foreach ($pluginRuntimeConfigFiles as $file) {
    $relative = str_replace($root . '/', '', $file);
    $report[] = '- ' . $relative;

    try {
        $config = require $file;
    } catch (Throwable $exception) {
        $report[] = '  ERROR load failed: ' . $exception->getMessage();
        $errors++;
        continue;
    }

    if (!is_array($config)) {
        $report[] = '  ERROR config must return array, got ' . get_debug_type($config);
        $errors++;
        continue;
    }

    $runtime = $config['method_plugins'] ?? $config['method_plugin_runtime'] ?? [];
    if ($runtime !== [] && !is_array($runtime)) {
        $report[] = '  ERROR method plugin runtime config must be array when present';
        $errors++;
        continue;
    }

    if (is_array($runtime) && isset($runtime['allow_list']) && !is_array($runtime['allow_list'])) {
        $report[] = '  ERROR allow_list must be array when present';
        $errors++;
    }
}

$config = \Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();
$report[] = '';
$report[] = '### Runtime default guard';
$report[] = '- default runtime enabled: ' . ($config->enabled ? 'yes' : 'no');
$report[] = '- default allow-list count: ' . count($config->reportOnlyInvocationKeys);
if ($config->enabled || count($config->reportOnlyInvocationKeys) !== 0) {
    $errors++;
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Selected service invoked: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/bootstrap-config-drift-audit.txt', implode("\n", $report) . "\n");
file_put_contents(
    $reportDir . '/bootstrap-config-drift-audit.log',
    "BOOTSTRAP_CONFIG_DRIFT_WARNINGS {$warnings}\n" .
    "BOOTSTRAP_CONFIG_DRIFT_ERRORS {$errors}\n"
);

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
