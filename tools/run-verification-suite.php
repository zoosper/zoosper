<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$php = PHP_BINARY;
$output = null;
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output=')) {
        $output = substr($argument, strlen('--output='));
    }
}
$output = $output ?: 'var/reports/verification-' . gmdate('Ymd-His') . '.txt';
$outputPath = str_starts_with($output, '/') ? $output : $basePath . '/' . $output;
if (!is_dir(dirname($outputPath))) {
    mkdir(dirname($outputPath), 0775, true);
}

$commands = [
    ['Syntax: AdminUserLocalePreferenceFieldRenderer.php', [$php, '-l', 'app/zoosper-admin/src/I18n/AdminUserLocalePreferenceFieldRenderer.php']],
    ['Syntax: apply-admin-user-locale-preference-ui.php', [$php, '-l', 'tools/apply-admin-user-locale-preference-ui.php']],
    ['Syntax: diagnose-admin-user-locale-preference-ui.php', [$php, '-l', 'tools/diagnose-admin-user-locale-preference-ui.php']],
    ['Syntax: verify-admin-user-locale-preference-ui.php', [$php, '-l', 'tools/verify-admin-user-locale-preference-ui.php']],
    ['Syntax: run-verification-suite.php', [$php, '-l', 'tools/run-verification-suite.php']],
    ['Verify: admin user locale preference UI', [$php, 'tools/verify-admin-user-locale-preference-ui.php']],
    ['Verify: supported admin locales', [$php, 'tools/verify-supported-admin-locales.php']],
    ['Verify: admin context translator resolution', [$php, 'tools/verify-admin-context-translator-resolution.php']],
    ['Verify: admin translator injected runtime', [$php, 'tools/verify-admin-translator-injected-runtime.php']],
    ['Verify: admin user locale hydration', [$php, 'tools/verify-admin-user-locale-hydration.php']],
    ['Verify: page SEO metadata', [$php, 'tools/verify-page-seo-metadata.php']],
    ['Verify: service providers', [$php, 'tools/verify-service-providers.php']],
];

$report = ['Zoosper verification suite report', '=================================', 'Started: ' . gmdate('c')];
$ok = true;
$summary = [];
foreach ($commands as $index => [$label, $command]) {
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $basePath);
    $stdout = '';
    $stderr = '';
    $exit = 127;
    if (is_resource($process)) {
        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exit = proc_close($process);
    }
    $passed = $exit === 0;
    $ok = $ok && $passed;
    $summary[] = '- ' . $label . ': ' . ($passed ? 'ok' : 'FAIL');
    $report[] = '[' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . '] ' . $label;
    $report[] = 'Command: ' . implode(' ', array_map('escapeshellarg', $command));
    $report[] = 'Exit code: ' . $exit;
    $report[] = '--- STDOUT ---';
    $report[] = rtrim($stdout);
    $report[] = '--- STDERR ---';
    $report[] = rtrim($stderr);
    $report[] = str_repeat('-', 80);
}
array_splice($report, 3, 0, 'Overall result: ' . ($ok ? 'OK' : 'FAIL'));
$report[4] = 'Finished: ' . gmdate('c');
file_put_contents($outputPath, implode(PHP_EOL, $report) . PHP_EOL);

print "Zoosper verification suite\n==========================\n";
print 'Report: ' . $outputPath . PHP_EOL;
print 'Overall result: ' . ($ok ? 'OK' : 'FAIL') . PHP_EOL;
foreach ($summary as $line) {
    print $line . PHP_EOL;
}
exit($ok ? 0 : 2);
