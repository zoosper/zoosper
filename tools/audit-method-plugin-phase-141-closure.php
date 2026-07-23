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

$report[] = '## Phase 1.41 Method Plugin Foundation Closure Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$requiredClasses = [
    'Zoosper\\Core\\Plugin\\MethodInvocation',
    'Zoosper\\Core\\Plugin\\MethodInterceptorInterface',
    'Zoosper\\Core\\Plugin\\CallableMethodInterceptor',
    'Zoosper\\Core\\Plugin\\MethodInterceptorChain',
    'Zoosper\\Core\\Plugin\\MethodPluginDefinition',
    'Zoosper\\Core\\Plugin\\MethodPluginRegistry',
    'Zoosper\\Core\\Plugin\\MethodPluginConfigLoader',
    'Zoosper\\Core\\Plugin\\MethodPluginFactory',
    'Zoosper\\Core\\Plugin\\MethodPluginExecutor',
    'Zoosper\\Core\\Plugin\\MethodPluginFileConfigLoader',
    'Zoosper\\Core\\Plugin\\MethodPluginConfigSource',
    'Zoosper\\Core\\Plugin\\MethodPluginConfigSourceDiscovery',
    'Zoosper\\Core\\Plugin\\MethodPluginModuleConfigLoader',
    'Zoosper\\Core\\Plugin\\MethodPluginResolverInterface',
    'Zoosper\\Core\\Plugin\\ReflectionMethodPluginResolver',
    'Zoosper\\Core\\Plugin\\MethodPluginException',
    'Zoosper\\Core\\Plugin\\MethodPluginValidationIssue',
    'Zoosper\\Core\\Plugin\\MethodPluginValidationResult',
    'Zoosper\\Core\\Plugin\\MethodPluginConfigValidator',
    'Zoosper\\Core\\Plugin\\MethodPluginReportOnlyResult',
    'Zoosper\\Core\\Plugin\\MethodPluginReportSinkInterface',
    'Zoosper\\Core\\Plugin\\InMemoryMethodPluginReportSink',
    'Zoosper\\Core\\Plugin\\ReportOnlyMethodPluginExecutor',
    'Zoosper\\Core\\Plugin\\MethodPluginRuntimeConfig',
    'Zoosper\\Core\\Plugin\\MethodPluginRuntime',
];

$report[] = '### Required plugin classes';
foreach ($requiredClasses as $class) {
    $exists = class_exists($class) || interface_exists($class);
    $report[] = '- ' . $class . ': ' . ($exists ? 'yes' : 'no');
    if (!$exists) {
        $errors++;
    }
}

$requiredTools = [
    'tools/audit-method-plugin-foundation.php',
    'tools/prove-method-plugin-sample-service.php',
    'tools/audit-method-plugin-discovery.php',
    'tools/prove-method-plugin-module-discovery.php',
    'tools/audit-method-plugin-module-discovery.php',
    'tools/prove-method-plugin-resolver-factory.php',
    'tools/audit-method-plugin-resolver-factory.php',
    'tools/prove-method-plugin-diagnostics.php',
    'tools/audit-method-plugin-diagnostics.php',
    'tools/prove-method-plugin-report-only-executor.php',
    'tools/audit-method-plugin-report-only-executor.php',
    'tools/prove-method-plugin-runtime-seam.php',
    'tools/audit-method-plugin-runtime-seam.php',
];

$report[] = '';
$report[] = '### Required proof/audit tools';
foreach ($requiredTools as $tool) {
    $exists = is_file($root . '/' . $tool);
    $report[] = '- ' . $tool . ': ' . ($exists ? 'exists' : 'missing');
    if (!$exists) {
        $errors++;
    }
}

$disabledConfig = \Zoosper\Core\Plugin\MethodPluginRuntimeConfig::disabled();
$report[] = '';
$report[] = '### Runtime disabled-by-default guard';
$report[] = '- default enabled: ' . ($disabledConfig->enabled ? 'yes' : 'no');
$report[] = '- default report-only: ' . ($disabledConfig->reportOnly ? 'yes' : 'no');
$report[] = '- default allow-list count: ' . count($disabledConfig->reportOnlyInvocationKeys);

if ($disabledConfig->enabled || !$disabledConfig->reportOnly || count($disabledConfig->reportOnlyInvocationKeys) !== 0) {
    $errors++;
}

$pluginFiles = glob($root . '/app/zoosper-core/src/Plugin/*.php') ?: [];
$sourceWarnings = [];
foreach ($pluginFiles as $file) {
    $source = (string) file_get_contents($file);
    if (preg_match('/new\s+ReportOnlyMethodPluginExecutor\s*\(/', $source) && !str_contains($file, 'MethodPluginRuntime.php')) {
        $sourceWarnings[] = str_replace($root . '/', '', $file) . ' constructs ReportOnlyMethodPluginExecutor directly';
    }
}

$report[] = '';
$report[] = '### Source drift warnings';
if ($sourceWarnings === []) {
    $report[] = '- none';
} else {
    foreach ($sourceWarnings as $warning) {
        $report[] = '- ' . $warning;
        $warnings++;
    }
}

$report[] = '';
$report[] = 'Runtime paths intercepted by default: no';
$report[] = 'Warnings: ' . $warnings;
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-phase-141-closure.txt', implode("\n", $report) . "\n");
file_put_contents(
    $reportDir . '/method-plugin-phase-141-closure.log',
    "METHOD_PLUGIN_PHASE_141_CLOSURE_WARNINGS {$warnings}\n" .
    "METHOD_PLUGIN_PHASE_141_CLOSURE_ERRORS {$errors}\n"
);

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
