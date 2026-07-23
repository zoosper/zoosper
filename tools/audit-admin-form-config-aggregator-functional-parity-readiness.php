<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$aggregatorClass = 'Zoosper\\Admin\\Form\\AdminFormConfigAggregator';
$bridgeClass = 'Zoosper\\Admin\\Form\\AdminConfigLayeredFileLoader';
$target = $root . '/app/zoosper-admin/src/Form/AdminFormConfigAggregator.php';
$errors = 0;
$report = [];

$report[] = '## AdminFormConfigAggregator Functional Parity Readiness Audit';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Aggregator class exists: ' . (class_exists($aggregatorClass) ? 'yes' : 'no');
$report[] = 'Bridge class exists: ' . (class_exists($bridgeClass) ? 'yes' : 'no');
$report[] = 'Aggregator source exists: ' . (is_file($target) ? 'yes' : 'no');

if (!class_exists($aggregatorClass) || !class_exists($bridgeClass) || !is_file($target)) {
    $errors++;
} else {
    $source = (string) file_get_contents($target);
    $remainingRequireAssignments = preg_match_all('/\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*require\b/', $source, $matches);
    $hasBridge = str_contains($source, 'AdminConfigLayeredFileLoader');
    $hasMarker = str_contains($source, 'PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED');
    $hasHelper = str_contains($source, 'loadLayeredAdminFormConfigFile');

    $reflection = new ReflectionClass($aggregatorClass);
    $constructor = $reflection->getConstructor();
    $publicMethods = [];

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!$method->isConstructor() && !$method->isDestructor()) {
            $publicMethods[] = $method->getName();
        }
    }

    $report[] = '';
    $report[] = '### Wiring readiness';
    $report[] = '- has AdminConfigLayeredFileLoader reference: ' . ($hasBridge ? 'yes' : 'no');
    $report[] = '- has phase marker: ' . ($hasMarker ? 'yes' : 'no');
    $report[] = '- has helper: ' . ($hasHelper ? 'yes' : 'no');
    $report[] = '- remaining require assignments: ' . (string) $remainingRequireAssignments;

    $report[] = '';
    $report[] = '### Functional parity planning signals';
    $report[] = '- constructor required parameters: ' . ($constructor ? (string) $constructor->getNumberOfRequiredParameters() : '0');
    $report[] = '- public methods: ' . implode(', ', $publicMethods);

    $ready = $hasBridge && $hasMarker && $hasHelper && $remainingRequireAssignments === 0;
    $report[] = '- ready for direct fixture parity phase: ' . ($ready ? 'yes' : 'no');

    if (!$ready) {
        $errors++;
    }
}

$report[] = '';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/admin-form-config-aggregator-functional-parity-readiness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/admin-form-config-aggregator-functional-parity-readiness.log', "ADMIN_FORM_CONFIG_AGGREGATOR_FUNCTIONAL_PARITY_READINESS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
