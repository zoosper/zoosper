<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$selectedFile = $root . '/var/reports/method-plugin-selected-report-only-candidate.json';
$harnessFile = $root . '/var/reports/method-plugin-selected-candidate-dry-run-harness.json';
$errors = 0;
$report = [];

$report[] = '## Method Plugin Selected Candidate Risk Notes';
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
        $riskLevel = 'medium';
        $key = (string) ($candidate['key'] ?? '');
        $file = (string) ($candidate['file'] ?? '');

        if (str_contains($key, 'Auth\\') || str_contains($key, 'Session') || str_contains($key, 'Password') || str_contains($key, 'Csrf')) {
            $riskLevel = 'high';
        } elseif (str_contains($file, '/Service/') && str_contains($key, 'render')) {
            $riskLevel = 'medium';
        }

        $notes = [
            'invocationKey' => $key,
            'class' => $candidate['class'] ?? '',
            'method' => $candidate['method'] ?? '',
            'file' => $file,
            'score' => $candidate['score'] ?? 0,
            'riskLevel' => $riskLevel,
            'runtimeDefaultEnabled' => false,
            'productionInterceptionEnabled' => false,
            'requiresFixtureInput' => true,
            'riskNotes' => [
                'Report-only proof must return the baseline/original result to callers.',
                'The selected invocation key must not be added to default runtime configuration.',
                'Any future proof must use explicit fixture/sample input before touching live request paths.',
                'If output differs, record the difference only; do not enforce plugin output.',
            ],
            'rollbackPrinciple' => 'Remove candidate-specific report-only proof files/config and revert to MethodPluginRuntimeConfig::disabled().',
        ];

        $report[] = 'Invocation key: ' . $notes['invocationKey'];
        $report[] = 'File: ' . $notes['file'];
        $report[] = 'Risk level: ' . $notes['riskLevel'];
        $report[] = 'Runtime default enabled: no';
        $report[] = 'Production interception enabled: no';
        $report[] = 'Requires fixture input: yes';
        $report[] = 'Dry-run harness exists: ' . (is_file($harnessFile) ? 'yes' : 'no');

        $reportDir = $root . '/var/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0775, true);
        }
        file_put_contents($reportDir . '/method-plugin-selected-candidate-risk-notes.json', json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-selected-candidate-risk-notes.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-risk-notes.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_RISK_NOTES_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
