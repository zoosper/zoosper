<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$signatureFile = $root . '/var/reports/method-plugin-selected-candidate-signature.json';
$errors = 0;
$report = [];

$report[] = '## Method Plugin Selected Candidate Fixture Contract Refinement';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($signatureFile)) {
    $report[] = 'Signature JSON missing. Run tools/discover-method-plugin-selected-candidate-signature.php first.';
    $errors++;
} else {
    $signature = json_decode((string) file_get_contents($signatureFile), true);

    if (!is_array($signature)) {
        $report[] = 'Signature JSON could not be decoded.';
        $errors++;
    } else {
        $arguments = [];
        foreach (($signature['parameters'] ?? []) as $parameter) {
            if (!is_array($parameter)) {
                continue;
            }

            $type = (string) ($parameter['type'] ?? 'mixed');
            $placeholder = 'fixture-required';

            if (($parameter['optional'] ?? false) === true) {
                $placeholder = 'fixture-optional-default-ok';
            } elseif (in_array($type, ['string', '?string'], true)) {
                $placeholder = 'fixture-string';
            } elseif (in_array($type, ['int', '?int'], true)) {
                $placeholder = 'fixture-int';
            } elseif (in_array($type, ['bool', '?bool'], true)) {
                $placeholder = 'fixture-bool';
            } elseif (in_array($type, ['array', '?array'], true)) {
                $placeholder = 'fixture-array';
            } elseif ($type !== 'mixed') {
                $placeholder = 'fixture-object:' . $type;
            }

            $arguments[] = [
                'name' => $parameter['name'] ?? '',
                'position' => $parameter['position'] ?? 0,
                'type' => $type,
                'required' => !($parameter['optional'] ?? false),
                'allowsNull' => (bool) ($parameter['allowsNull'] ?? false),
                'placeholder' => $placeholder,
                'liveDataAllowed' => false,
            ];
        }

        $contract = [
            'invocationKey' => $signature['invocationKey'] ?? '',
            'class' => $signature['class'] ?? '',
            'method' => $signature['method'] ?? '',
            'runtimeDefaultEnabled' => false,
            'productionInvocationEnabled' => false,
            'fixtureOnly' => true,
            'serviceInvoked' => false,
            'fixtureStatus' => 'signature-refined-contract-only',
            'arguments' => $arguments,
            'returnType' => $signature['returnType'] ?? 'mixed',
            'outputPolicy' => [
                'returnToCaller' => 'baseline-result-only',
                'pluginOutput' => 'report-only-observation',
                'enforcement' => false,
            ],
        ];

        $report[] = 'Invocation key: ' . $contract['invocationKey'];
        $report[] = 'Argument count: ' . count($arguments);
        $report[] = 'Return type: ' . $contract['returnType'];
        foreach ($arguments as $argument) {
            $report[] = '- argument $' . $argument['name'] . ': type=' . $argument['type'] . ', required=' . ($argument['required'] ? 'yes' : 'no') . ', placeholder=' . $argument['placeholder'];
        }
        $report[] = 'Fixture only: yes';
        $report[] = 'Service invoked: no';

        $reportDir = $root . '/var/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0775, true);
        }
        file_put_contents($reportDir . '/method-plugin-selected-candidate-fixture-contract-refined.json', json_encode($contract, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
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
file_put_contents($reportDir . '/method-plugin-selected-candidate-fixture-contract-refined.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-fixture-contract-refined.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_FIXTURE_CONTRACT_REFINED_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
