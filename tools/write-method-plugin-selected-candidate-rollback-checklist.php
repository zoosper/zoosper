<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$riskFile = $root . '/var/reports/method-plugin-selected-candidate-risk-notes.json';
$errors = 0;
$report = [];

$report[] = '## Method Plugin Selected Candidate Rollback Checklist';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($riskFile)) {
    $report[] = 'Risk notes JSON missing. Run tools/write-method-plugin-selected-candidate-risk-notes.php first.';
    $errors++;
} else {
    $risk = json_decode((string) file_get_contents($riskFile), true);
    if (!is_array($risk)) {
        $report[] = 'Risk notes JSON could not be decoded.';
        $errors++;
    } else {
        $checklist = [
            'invocationKey' => $risk['invocationKey'] ?? '',
            'productionInterceptionEnabled' => false,
            'rollbackChecklist' => [
                'Confirm MethodPluginRuntimeConfig::disabled() remains the default.',
                'Remove any candidate-specific allow-list entry if it was added in a future phase.',
                'Remove candidate-specific fixture/proof files if they cause failures.',
                'Run method plugin closure and full Pest gates.',
                'Reload the affected route/page only after gates are green.',
                'Check nginx and var/log/exception.log for runtime fatals.',
            ],
            'preFlightGuards' => [
                'No production runtime interception by default.',
                'Report-only output must never replace baseline output.',
                'Fixture/sample input required before any candidate invocation proof.',
            ],
        ];

        $report[] = 'Invocation key: ' . $checklist['invocationKey'];
        $report[] = 'Production interception enabled: no';
        $report[] = 'Rollback checklist items: ' . count($checklist['rollbackChecklist']);
        $report[] = 'Pre-flight guards: ' . count($checklist['preFlightGuards']);

        foreach ($checklist['rollbackChecklist'] as $item) {
            $report[] = '- rollback: ' . $item;
        }
        foreach ($checklist['preFlightGuards'] as $item) {
            $report[] = '- guard: ' . $item;
        }

        $reportDir = $root . '/var/reports';
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0775, true);
        }
        file_put_contents($reportDir . '/method-plugin-selected-candidate-rollback-checklist.json', json_encode($checklist, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    }
}

$report[] = '';
$report[] = 'Production runtime interception enabled: no';
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-selected-candidate-rollback-checklist.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-candidate-rollback-checklist.log', "METHOD_PLUGIN_SELECTED_CANDIDATE_ROLLBACK_CHECKLIST_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
