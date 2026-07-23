<?php

declare(strict_types=1);

/**
 * Read-only smoke for admin UI/form config layering.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

require_once $root . '/app/zoosper-core/src/Config/LayeredConfigResult.php';
require_once $root . '/app/zoosper-core/src/Config/LayeredConfigLoader.php';
require_once $root . '/app/zoosper-core/src/Config/ConfigLayerSource.php';
require_once $root . '/app/zoosper-core/src/Config/ConfigFileLayeredLoader.php';

use Zoosper\Core\Config\ConfigFileLayeredLoader;
use Zoosper\Core\Config\ConfigLayerSource;

$candidates = [
    'app/zoosper-page/config/admin_forms.php',
    'app/zoosper-page/config/admin_ui.php',
    'app/zoosper-auth/config/admin_ui.php',
    'app/zoosper-admin/config/editor.php',
];

$sources = [];
$errors = [];
foreach ($candidates as $relative) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (! is_file($path)) {
        continue;
    }
    $sources[] = new ConfigLayerSource($relative, $path);
}

$result = null;
try {
    $result = (new ConfigFileLayeredLoader())->load($sources);
} catch (Throwable $exception) {
    $errors[] = $exception::class . ': ' . $exception->getMessage();
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'admin-ui-form-config-layering-smoke.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'admin-ui-form-config-layering-smoke.log';

$report = [];
$report[] = '# Admin UI/Form Config Layering Smoke';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Candidate files: ' . count($candidates);
$report[] = 'Existing source files: ' . count($sources);
$report[] = 'Merged top-level keys: ' . ($result ? count($result->config) : 0);
$report[] = '';
$report[] = '## Sources';
foreach ($sources as $source) {
    $report[] = '- ' . $source->source;
}
if ($result) {
    $report[] = '';
    $report[] = '## Top-level keys';
    foreach (array_keys($result->config) as $key) {
        $report[] = '- ' . (string) $key;
    }
}
if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}
file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Admin UI/form config layering smoke written to: ' . $reportPath;
$log[] = 'ADMIN_UI_FORM_CONFIG_LAYERING_SMOKE_ERRORS ' . count($errors);
$log[] = 'ADMIN_UI_FORM_CONFIG_LAYERING_SOURCES ' . count($sources);
$log[] = 'ADMIN_UI_FORM_CONFIG_LAYERING_TOP_LEVEL_KEYS ' . ($result ? count($result->config) : 0);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
