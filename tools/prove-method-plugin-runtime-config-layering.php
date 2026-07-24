<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\MethodPluginRuntimeConfigLayeredLoader;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$errors = 0;
$report = [];
$report[] = '## Method Plugin Runtime Config Layering Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$tmp = sys_get_temp_dir() . '/zoosper-method-plugin-runtime-config-' . bin2hex(random_bytes(6));
mkdir($tmp, 0775, true);

$moduleFile = $tmp . '/module-method_plugins.php';
$rootFile = $tmp . '/root-method_plugins.php';

file_put_contents($moduleFile, "<?php\nreturn [\n    'method_plugins' => [\n        'enabled' => true,\n        'report_only' => true,\n        'allow_list' => ['Zoosper\\\\Page\\\\Service\\\\PageRenderer::render'],\n    ],\n];\n");
file_put_contents($rootFile, "<?php\nreturn [\n    'method_plugins' => [\n        'enabled' => false,\n        'report_only' => true,\n        'allow_list' => [],\n    ],\n];\n");

try {
    $config = (new MethodPluginRuntimeConfigLayeredLoader())->load([
        'module:test-method-plugins' => $moduleFile,
        'root:test-method-plugins' => $rootFile,
    ]);

    $report[] = 'Resolved runtime enabled: ' . ($config->enabled ? 'yes' : 'no');
    $report[] = 'Resolved report-only: ' . ($config->reportOnly ? 'yes' : 'no');
    $report[] = 'Resolved allow-list count: ' . count($config->reportOnlyInvocationKeys);
    $report[] = 'Production runtime interception enabled: no';
    $report[] = 'Selected service invoked: no';

    if ($config->enabled || !$config->reportOnly || count($config->reportOnlyInvocationKeys) !== 0) {
        $errors++;
    }
} catch (Throwable $exception) {
    $report[] = 'Exception: ' . $exception->getMessage();
    $errors++;
}

$report[] = 'Runtime config layering proof: ' . ($errors === 0 ? 'yes' : 'no');
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-runtime-config-layering-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-runtime-config-layering-proof.log', "METHOD_PLUGIN_RUNTIME_CONFIG_LAYERING_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
