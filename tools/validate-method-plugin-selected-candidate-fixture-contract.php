<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$contractFile = $root . '/var/reports/method-plugin-selected-candidate-fixture-contract.json';
$errors = 0;
$report = [];

$report[] = '## Method Plugin Selected Candidate Fixture Contract Validation';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($contractFile)) {
    $report[] = 'Fixture contract JSON missing. Run tools/write-method-plugin-selected-candidate-fixture-contract.php first.';
    $errors++;
} else {
    $contract = json_decode((string) file_get_contents($contractFile), true);
    if (!is_array($contract)) {
        $report[] = 'Fixture contract JSON could not be decoded.';
        $errors++;
    } else {
        $checks = [
            'invocationKey present' => isset($contract['invocationKey']) && is_string($contract['invocationKey']) && $contract['invocationKey'] !== '',
            'runtime default disabled' => ($contract['runtimeDefaultEnabled'] ?? true) === false,
            'production invocation disabled' => ($contract['productionInvocationEnabled'] ?? true) === false,
            'fixture only' => ($contract['fixtureOnly'] ?? false) === true,
            'input contract required' => (($contract['inputContract']['required'] ?? false) === true),
            'baseline output policy' => (($contract['outputPolicy']['returnToCaller'] ?? '') === 'baseline-result-only'),
            'no enforcement' => (($contract['outputPolicy']['enforcement'] ?? true) === false),
        ];

        foreach ($checks as $label => $passed) {
            $report[] = '- ' . $label . ': ' . ($passed ? 'yes' : 'no');
            if (!$passed) {
                $errors++;
            }
        }
    }
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-selected-candidate-fixture-contract-validation.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-fixture-contract-validation.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_FIXTURE_CONTRACT_VALIDATION_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
