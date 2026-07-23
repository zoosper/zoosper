<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$selectedFile = $root . '/var/reports/method-plugin-selected-report-only-candidate.json';
$errors = 0;
$report = [];

$report[] = '## Method Plugin Selected Candidate Dry-Run Harness Plan';
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
        $harness = [
            'invocationKey' => $candidate['key'] ?? '',
            'class' => $candidate['class'] ?? '',
            'method' => $candidate['method'] ?? '',
            'sourceFile' => $candidate['file'] ?? '',
            'runtimeDefaultEnabled' => false,
            'mode' => 'report-only-dry-run-plan',
            'productionInvocationEnabled' => false,
            'fixtureInputRequired' => true,
            'notes' => [
                'This harness does not invoke the production service.',
                'This harness does not add the invocation key to runtime config.',
                'A future phase must provide explicit fixture input before invoking any candidate method.',
            ],
        ];

        $report[] = 'Invocation key: ' . $harness['invocationKey'];
        $report[] = 'Class: ' . $harness['class'];
        $report[] = 'Method: ' . $harness['method'];
        $report[] = 'Source file: ' . $harness['sourceFile'];
        $report[] = 'Runtime default enabled: no';
        $report[] = 'Production invocation enabled: no';
        $report[] = 'Fixture input required: yes';

        $reportDir = $root . '/var/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0775, true);
        }
        file_put_contents($reportDir . '/method-plugin-selected-candidate-dry-run-harness.json', json_encode($harness, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-selected-candidate-dry-run-harness.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-dry-run-harness.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_DRY_RUN_HARNESS_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
