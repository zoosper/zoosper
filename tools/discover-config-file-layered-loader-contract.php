<?php

declare(strict_types=1);


$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$class = 'Zoosper\\Core\\Config\\ConfigFileLayeredLoader';
$report = [];
$errors = 0;

$report[] = '## ConfigFileLayeredLoader Contract Discovery';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Class: ' . $class;
$report[] = 'Class exists: ' . (class_exists($class) ? 'yes' : 'no');

if (!class_exists($class)) {
    $errors++;
} else {
    $reflection = new ReflectionClass($class);
    $report[] = 'File: ' . (string) $reflection->getFileName();
    $constructor = $reflection->getConstructor();
    $report[] = 'Constructor parameters: ' . ($constructor ? (string) $constructor->getNumberOfParameters() : '0');
    $report[] = '';
    $report[] = '### Public methods';

    foreach ($reflection->getMethods() as $method) {
        if (!$method->isPublic() || $method->isConstructor() || $method->isDestructor()) {
            continue;
        }

        $params = [];
        foreach ($method->getParameters() as $parameter) {
            $params[] = '$' . $parameter->getName() . ($parameter->isOptional() ? ' = optional' : '');
        }

        $report[] = '- ' . $method->getName() . '(' . implode(', ', $params) . ')';
    }
}

$report[] = '';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/config-file-layered-loader-contract.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/config-file-layered-loader-contract.log', "CONFIG_FILE_LAYERED_LOADER_CONTRACT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
