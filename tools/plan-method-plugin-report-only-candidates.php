<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$candidatesFile = $root . '/var/reports/method-plugin-service-candidates.json';
$errors = 0;
$report = [];

$report[] = '## Method Plugin Report-Only Candidate Plan';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($candidatesFile)) {
    $report[] = 'Candidate report missing. Run tools/discover-method-plugin-service-candidates.php first.';
    $errors++;
} else {
    $candidates = json_decode((string) file_get_contents($candidatesFile), true);
    if (!is_array($candidates)) {
        $report[] = 'Candidate report could not be decoded.';
        $errors++;
    } else {
        $planned = array_slice(array_values(array_filter($candidates, static fn (array $candidate): bool => ($candidate['score'] ?? 0) >= 2)), 0, 10);
        $report[] = 'Candidates considered: ' . count($candidates);
        $report[] = 'Planned report-only candidates: ' . count($planned);
        $report[] = '';
        foreach ($planned as $candidate) {
            $report[] = '- invocationKey: ' . $candidate['key'];
            $report[] = '  file: ' . $candidate['file'];
            $report[] = '  score: ' . $candidate['score'];
            $report[] = '  defaultEnabled: no';
            $report[] = '  futureMode: report-only';
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
file_put_contents($reportDir . '/method-plugin-report-only-candidate-plan.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-report-only-candidate-plan.log', "METHOD_PLUGIN_REPORT_ONLY_CANDIDATE_PLAN_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
