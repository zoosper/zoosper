<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$candidatesFile = $root . '/var/reports/method-plugin-service-candidates.json';
$errors = 0;
$report = [];

$report[] = '## Method Plugin Report-Only Candidate Selection';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

if (!is_file($candidatesFile)) {
    $report[] = 'Candidate JSON missing. Run tools/discover-method-plugin-service-candidates.php first.';
    $errors++;
} else {
    $candidates = json_decode((string) file_get_contents($candidatesFile), true);

    if (!is_array($candidates)) {
        $report[] = 'Candidate JSON could not be decoded.';
        $errors++;
    } else {
        $eligible = array_values(array_filter($candidates, static function (array $candidate): bool {
            $key = (string) ($candidate['key'] ?? '');
            $file = (string) ($candidate['file'] ?? '');
            $score = (int) ($candidate['score'] ?? 0);

            if ($score < 2) {
                return false;
            }

            // Avoid auth/session/security hot paths for the first report-only proof.
            foreach (['Auth\\Service', 'SessionGuard', 'PasswordHasher', 'CsrfTokenManager', 'Http\\', 'Repository'] as $risky) {
                if (str_contains($key, $risky) || str_contains($file, $risky)) {
                    return false;
                }
            }

            return true;
        }));

        usort($eligible, static fn (array $a, array $b): int => [$b['score'], $a['key']] <=> [$a['score'], $b['key']]);
        $selected = $eligible[0] ?? null;

        $report[] = 'Candidates considered: ' . count($candidates);
        $report[] = 'Eligible safe candidates: ' . count($eligible);

        if (!is_array($selected)) {
            $report[] = 'Selected candidate: none';
            $errors++;
        } else {
            $report[] = 'Selected invocation key: ' . $selected['key'];
            $report[] = 'Selected file: ' . $selected['file'];
            $report[] = 'Selected score: ' . $selected['score'];
            $report[] = 'Default enabled: no';
            $report[] = 'Future proof mode: report-only';

            $reportDir = $root . '/var/reports';
            if (!is_dir($reportDir)) {
                mkdir($reportDir, 0775, true);
            }
            file_put_contents($reportDir . '/method-plugin-selected-report-only-candidate.json', json_encode($selected, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
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
file_put_contents($reportDir . '/method-plugin-selected-report-only-candidate.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-selected-report-only-candidate.log', "METHOD_PLUGIN_SELECTED_REPORT_ONLY_CANDIDATE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
