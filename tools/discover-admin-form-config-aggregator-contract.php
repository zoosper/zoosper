<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$class = 'Zoosper\\Admin\\Form\\AdminFormConfigAggregator';
$report = [];
$errors = 0;

$report[] = '## AdminFormConfigAggregator Contract Discovery';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Class: ' . $class;
$exists = class_exists($class);
$report[] = 'Class exists: ' . ($exists ? 'yes' : 'no');

if (!$exists) {
    $errors++;
} else {
    $reflection = new ReflectionClass($class);
    $fileName = (string) $reflection->getFileName();
    $source = (string) file_get_contents($fileName);

    $report[] = 'File: ' . $fileName;

    $constructor = $reflection->getConstructor();
    $report[] = 'Constructor parameters: ' . ($constructor ? (string) $constructor->getNumberOfParameters() : '0');

    if ($constructor) {
        $report[] = '';
        $report[] = '### Constructor details';
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            $report[] = sprintf(
                '- $%s type=%s optional=%s default=%s',
                $parameter->getName(),
                $type ? (string) $type : 'none',
                $parameter->isOptional() ? 'yes' : 'no',
                $parameter->isDefaultValueAvailable() ? var_export($parameter->getDefaultValue(), true) : 'none'
            );
        }
    }

    $report[] = '';
    $report[] = '### Public methods';
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->isConstructor() || $method->isDestructor()) {
            continue;
        }

        $params = [];
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            $params[] = '$' . $parameter->getName() . ':' . ($type ? (string) $type : 'mixed') . ($parameter->isOptional() ? '=optional' : '');
        }

        $returnType = $method->getReturnType();
        $report[] = '- ' . $method->getName() . '(' . implode(', ', $params) . '): ' . ($returnType ? (string) $returnType : 'mixed');
    }

    $remainingRequireAssignments = preg_match_all('/\$[A-Za-z_][A-Za-z0-9_]*\s*=\s*require\b/', $source, $matches);

    $report[] = '';
    $report[] = '### Layered wiring signals';
    $report[] = '- has AdminConfigLayeredFileLoader reference: ' . (str_contains($source, 'AdminConfigLayeredFileLoader') ? 'yes' : 'no');
    $report[] = '- has phase marker: ' . (str_contains($source, 'PHASE_140QR_ADMIN_FORM_CONFIG_AGGREGATOR_LAYERED') ? 'yes' : 'no');
    $report[] = '- has loadLayeredAdminFormConfigFile helper: ' . (str_contains($source, 'loadLayeredAdminFormConfigFile') ? 'yes' : 'no');
    $report[] = '- remaining require assignments: ' . (string) $remainingRequireAssignments;
}

$report[] = '';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/admin-form-config-aggregator-contract.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/admin-form-config-aggregator-contract.log', "ADMIN_FORM_CONFIG_AGGREGATOR_CONTRACT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
