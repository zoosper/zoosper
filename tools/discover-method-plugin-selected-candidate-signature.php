<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$selectedFile = $root . '/var/reports/method-plugin-selected-report-only-candidate.json';
$errors = 0;
$report = [];
$signature = null;

$report[] = '## Method Plugin Selected Candidate Signature Discovery';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($selectedFile)) {
    $report[] = 'Selected candidate JSON missing. Run tools/select-method-plugin-report-only-candidate.php first.';
    $errors++;
} else {
    $candidate = json_decode((string) file_get_contents($selectedFile), true);

    if (!is_array($candidate)) {
        $report[] = 'Selected candidate JSON could not be decoded.';
        $errors++;
    } else {
        $class = (string) ($candidate['class'] ?? '');
        $method = (string) ($candidate['method'] ?? '');
        $invocationKey = (string) ($candidate['key'] ?? ($class . '::' . $method));

        $report[] = 'Invocation key: ' . $invocationKey;
        $report[] = 'Class: ' . $class;
        $report[] = 'Method: ' . $method;

        if ($class === '' || $method === '') {
            $report[] = 'Selected candidate class or method is empty.';
            $errors++;
        } elseif (!class_exists($class)) {
            $report[] = 'Selected candidate class does not exist via autoload: ' . $class;
            $errors++;
        } elseif (!method_exists($class, $method)) {
            $report[] = 'Selected candidate method does not exist: ' . $invocationKey;
            $errors++;
        } else {
            $reflection = new ReflectionMethod($class, $method);
            $parameters = [];

            foreach ($reflection->getParameters() as $parameter) {
                $type = $parameter->getType();
                $parameters[] = [
                    'name' => $parameter->getName(),
                    'type' => $type ? (string) $type : 'mixed',
                    'allowsNull' => $type ? $type->allowsNull() : true,
                    'optional' => $parameter->isOptional(),
                    'hasDefault' => $parameter->isDefaultValueAvailable(),
                    'default' => $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
                    'position' => $parameter->getPosition(),
                ];
            }

            $returnType = $reflection->getReturnType();
            $signature = [
                'invocationKey' => $invocationKey,
                'class' => $class,
                'method' => $method,
                'file' => $reflection->getFileName() ?: '',
                'startLine' => $reflection->getStartLine(),
                'endLine' => $reflection->getEndLine(),
                'isStatic' => $reflection->isStatic(),
                'isPublic' => $reflection->isPublic(),
                'parameterCount' => count($parameters),
                'parameters' => $parameters,
                'returnType' => $returnType ? (string) $returnType : 'mixed',
                'serviceInvoked' => false,
                'productionRuntimeInterceptionEnabled' => false,
            ];

            $report[] = 'Public: ' . ($reflection->isPublic() ? 'yes' : 'no');
            $report[] = 'Static: ' . ($reflection->isStatic() ? 'yes' : 'no');
            $report[] = 'Parameter count: ' . count($parameters);
            $report[] = 'Return type: ' . ($returnType ? (string) $returnType : 'mixed');
            foreach ($parameters as $parameter) {
                $report[] = '- parameter $' . $parameter['name'] . ': type=' . $parameter['type'] . ', optional=' . ($parameter['optional'] ? 'yes' : 'no') . ', allowsNull=' . ($parameter['allowsNull'] ? 'yes' : 'no');
            }

            $reportDir = $root . '/var/reports';
            if (!is_dir($reportDir)) {
                mkdir($reportDir, 0775, true);
            }
            file_put_contents($reportDir . '/method-plugin-selected-candidate-signature.json', json_encode($signature, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        }
    }
}

$report[] = '';
$report[] = 'Selected service invoked: no';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-selected-candidate-signature.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-signature.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_SIGNATURE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
