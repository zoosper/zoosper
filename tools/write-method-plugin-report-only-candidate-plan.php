<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$selectedFile = $root . '/var/reports/method-plugin-selected-report-only-candidate.json';
$errors = 0;
$report = [];

$report[] = '## Method Plugin Selected Candidate Report-Only Plan';
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
        $report[] = 'Invocation key: ' . $candidate['key'];
        $report[] = 'Class: ' . $candidate['class'];
        $report[] = 'Method: ' . $candidate['method'];
        $report[] = 'File: ' . $candidate['file'];
        $report[] = 'Score: ' . $candidate['score'];
        $report[] = '';
        $report[] = 'Plan:';
        $report[] = '- defaultEnabled: no';
        $report[] = '- runtimeMode: report-only';
        $report[] = '- allowListRequired: yes';
        $report[] = '- productionResultPolicy: return baseline result';
        $report[] = '- nextProofInput: explicit fixture/sample only';
    }
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-selected-report-only-candidate-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-report-only-candidate-plan.log', "METHOD_PLUGIN_SELECTED_REPORT_ONLY_CANDIDATE_PLAN_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
