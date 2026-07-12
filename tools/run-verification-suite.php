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

if ($output === null || $output === '') {
    $output = 'var/reports/verification-' . gmdate('Ymd-His') . '.txt';
}

$outputPath = str_starts_with($output, '/') ? $output : $basePath . '/' . $output;
$directory = dirname($outputPath);
if (!is_dir($directory)) {
    mkdir($directory, 0775, true);
}

$commands = zoosper_verification_commands($php);
$lines = [];
$lines[] = 'Zoosper verification suite report';
$lines[] = '=================================';
$lines[] = 'Started: ' . gmdate('c');
$overallOk = true;
$summary = [];

foreach ($commands as $index => $command) {
    $label = $command['label'];
    $process = proc_open($command['command'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $basePath);
    if (!is_resource($process)) {
        $exitCode = 127;
        $stdout = '';
        $stderr = 'Unable to start command.';
    } else {
        $stdout = stream_get_contents($pipes[1]) ?: '';
        $stderr = stream_get_contents($pipes[2]) ?: '';
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
    }

    $ok = $exitCode === 0;
    $overallOk = $overallOk && $ok;
    $summary[] = '- ' . $label . ': ' . ($ok ? 'ok' : 'FAIL');

    $lines[] = '[' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . '] ' . $label;
    $lines[] = 'Command: ' . implode(' ', array_map('escapeshellarg', $command['command']));
    $lines[] = 'Exit code: ' . $exitCode;
    $lines[] = '--- STDOUT ---';
    $lines[] = rtrim($stdout);
    $lines[] = '--- STDERR ---';
    $lines[] = rtrim($stderr);
    $lines[] = str_repeat('-', 80);
}

$lines[3] = 'Finished: ' . gmdate('c');
array_splice($lines, 3, 0, 'Overall result: ' . ($overallOk ? 'OK' : 'FAIL'));
file_put_contents($outputPath, implode(PHP_EOL, $lines) . PHP_EOL);

print "Zoosper verification suite\n";
print "==========================\n";
print 'Report: ' . $outputPath . PHP_EOL;
print 'Overall result: ' . ($overallOk ? 'OK' : 'FAIL') . PHP_EOL;
foreach ($summary as $item) {
    print $item . PHP_EOL;
}

exit($overallOk ? 0 : 2);

/** @return list<array{label: string, command: list<string>}> */
function zoosper_verification_commands(string $php): array
{
    return [
        ['label' => 'Syntax: admin_user_locale schema', 'command' => [$php, '-l', 'database/schema/admin_user_locale.php']],
        ['label' => 'Syntax: AdminUserLocaleResolver.php', 'command' => [$php, '-l', 'app/zoosper-core/src/I18n/AdminUserLocaleResolver.php']],
        ['label' => 'Syntax: apply-admin-user-locale-schema.php', 'command' => [$php, '-l', 'tools/apply-admin-user-locale-schema.php']],
        ['label' => 'Syntax: verify-admin-user-locale-preference.php', 'command' => [$php, '-l', 'tools/verify-admin-user-locale-preference.php']],
        ['label' => 'Syntax: diagnose-admin-user-locale-preference.php', 'command' => [$php, '-l', 'tools/diagnose-admin-user-locale-preference.php']],
        ['label' => 'Syntax: run-verification-suite.php', 'command' => [$php, '-l', 'tools/run-verification-suite.php']],
        ['label' => 'Verify: admin user locale preference', 'command' => [$php, 'tools/verify-admin-user-locale-preference.php']],
        ['label' => 'Verify: admin translator injected runtime', 'command' => [$php, 'tools/verify-admin-translator-injected-runtime.php']],
        ['label' => 'Verify: admin translator runtime wiring', 'command' => [$php, 'tools/verify-admin-translator-runtime-wiring.php']],
        ['label' => 'Verify: admin/site locale resolution', 'command' => [$php, 'tools/verify-admin-site-locale-resolution.php']],
        ['label' => 'Verify: i18n service provider registration', 'command' => [$php, 'tools/verify-i18n-service-provider-registration.php']],
        ['label' => 'Verify: Editor.js JSON save pipeline', 'command' => [$php, 'tools/verify-editorjs-json-save-pipeline.php']],
        ['label' => 'Verify: page SEO metadata', 'command' => [$php, 'tools/verify-page-seo-metadata.php']],
        ['label' => 'Verify: service providers', 'command' => [$php, 'tools/verify-service-providers.php']],
    ];
}
