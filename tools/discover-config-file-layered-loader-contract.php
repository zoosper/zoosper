<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$classes = [
    'Zoosper\\Core\\Config\\ConfigFileLayeredLoader',
    'Zoosper\\Core\\Config\\ConfigLayerSource',
    'Zoosper\\Core\\Config\\LayeredConfigLoader',
    'Zoosper\\Core\\Config\\LayeredConfigResult',
];

$report = [];
$errors = 0;
$report[] = '## Config Layering Runtime Contract Discovery';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

foreach ($classes as $class) {
    $report[] = '### ' . $class;
    $exists = class_exists($class);
    $report[] = '- class exists: ' . ($exists ? 'yes' : 'no');

    if (!$exists) {
        $errors++;
        $report[] = '';
        continue;
    }

    $reflection = new ReflectionClass($class);
    $report[] = '- file: ' . (string) $reflection->getFileName();

    $constructor = $reflection->getConstructor();
    $report[] = '- constructor parameters: ' . ($constructor ? (string) $constructor->getNumberOfParameters() : '0');

    if ($constructor) {
        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            $report[] = sprintf(
                '  - $%s type=%s optional=%s default=%s',
                $parameter->getName(),
                $type ? (string) $type : 'none',
                $parameter->isOptional() ? 'yes' : 'no',
                $parameter->isDefaultValueAvailable() ? var_export($parameter->getDefaultValue(), true) : 'none'
            );
        }
    }

    $report[] = '- public methods:';
    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if ($method->isConstructor() || $method->isDestructor()) {
            continue;
        }

        $params = [];
        foreach ($method->getParameters() as $parameter) {
            $type = $parameter->getType();
            $params[] = '$' . $parameter->getName() . ':' . ($type ? (string) $type : 'mixed');
        }

        $returnType = $method->getReturnType();
        $report[] = '  - ' . $method->getName() . '(' . implode(', ', $params) . '): ' . ($returnType ? (string) $returnType : 'mixed');
    }

    $report[] = '';
}

$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/config-layering-runtime-contract.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/config-layering-runtime-contract.log', "CONFIG_LAYERING_RUNTIME_CONTRACT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
