<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$selectedFile = $root . '/var/reports/method-plugin-selected-report-only-candidate.json';
$riskFile = $root . '/var/reports/method-plugin-selected-candidate-risk-notes.json';
$errors = 0;
$report = [];

$report[] = '## Method Plugin Selected Candidate Fixture Contract';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($selectedFile)) {
    $report[] = 'Selected candidate JSON missing. Run tools/select-method-plugin-report-only-candidate.php first.';
    $errors++;
} else {
    $candidate = json_decode((string) file_get_contents($selectedFile), true);
    $risk = is_file($riskFile) ? json_decode((string) file_get_contents($riskFile), true) : [];

    if (!is_array($candidate)) {
        $report[] = 'Selected candidate JSON could not be decoded.';
        $errors++;
    } else {
        $contract = [
            'invocationKey' => $candidate['key'] ?? '',
            'class' => $candidate['class'] ?? '',
            'method' => $candidate['method'] ?? '',
            'sourceFile' => $candidate['file'] ?? '',
            'runtimeDefaultEnabled' => false,
            'productionInvocationEnabled' => false,
            'fixtureOnly' => true,
            'fixtureStatus' => 'contract-only-no-service-invocation',
            'riskLevel' => is_array($risk) ? ($risk['riskLevel'] ?? 'unknown') : 'unknown',
            'inputContract' => [
                'type' => 'explicit-fixture-array',
                'required' => true,
                'arguments' => [],
                'notes' => [
                    'Future phase must add fixture arguments based on the selected method signature.',
                    'This contract intentionally contains no live request data.',
                    'This contract must not be used to enable production runtime interception.',
                ],
            ],
            'outputPolicy' => [
                'returnToCaller' => 'baseline-result-only',
                'pluginOutput' => 'report-only-observation',
                'enforcement' => false,
            ],
        ];

        $report[] = 'Invocation key: ' . $contract['invocationKey'];
        $report[] = 'Class: ' . $contract['class'];
        $report[] = 'Method: ' . $contract['method'];
        $report[] = 'Fixture only: yes';
        $report[] = 'Production invocation enabled: no';
        $report[] = 'Output policy: baseline-result-only';

        $reportDir = $root . '/var/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0775, true);
        }
        file_put_contents($reportDir . '/method-plugin-selected-candidate-fixture-contract.json', json_encode($contract, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-selected-candidate-fixture-contract.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-fixture-contract.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_FIXTURE_CONTRACT_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
