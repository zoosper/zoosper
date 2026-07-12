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
        ['label' => 'Syntax: config/i18n.php', 'command' => [$php, '-l', 'config/i18n.php']],
        ['label' => 'Syntax: config/service_providers.php', 'command' => [$php, '-l', 'config/service_providers.php']],
        ['label' => 'Syntax: ApplicationFactory.php', 'command' => [$php, '-l', 'app/zoosper-core/src/Bootstrap/ApplicationFactory.php']],
        ['label' => 'Syntax: I18nServiceProvider.php', 'command' => [$php, '-l', 'app/zoosper-core/src/I18n/I18nServiceProvider.php']],
        ['label' => 'Syntax: page controllers config', 'command' => [$php, '-l', 'app/zoosper-page/config/controllers.php']],
        ['label' => 'Syntax: apply-reduce-admin-translator-fallback.php', 'command' => [$php, '-l', 'tools/apply-reduce-admin-translator-fallback.php']],
        ['label' => 'Syntax: verify-admin-translator-injected-runtime.php', 'command' => [$php, '-l', 'tools/verify-admin-translator-injected-runtime.php']],
        ['label' => 'Syntax: verify-admin-translator-runtime-wiring.php', 'command' => [$php, '-l', 'tools/verify-admin-translator-runtime-wiring.php']],
        ['label' => 'Syntax: verify-admin-translator-resolution.php', 'command' => [$php, '-l', 'tools/verify-admin-translator-resolution.php']],
        ['label' => 'Syntax: verify-translatable-admin-system-messages.php', 'command' => [$php, '-l', 'tools/verify-translatable-admin-system-messages.php']],
        ['label' => 'Syntax: run-verification-suite.php', 'command' => [$php, '-l', 'tools/run-verification-suite.php']],
        ['label' => 'Verify: admin translator injected runtime', 'command' => [$php, 'tools/verify-admin-translator-injected-runtime.php']],
        ['label' => 'Verify: admin translator runtime wiring', 'command' => [$php, 'tools/verify-admin-translator-runtime-wiring.php']],
        ['label' => 'Verify: admin translator resolution', 'command' => [$php, 'tools/verify-admin-translator-resolution.php']],
        ['label' => 'Verify: translatable admin system messages', 'command' => [$php, 'tools/verify-translatable-admin-system-messages.php']],
        ['label' => 'Verify: admin translator container injection', 'command' => [$php, 'tools/verify-admin-translator-container-injection.php']],
        ['label' => 'Verify: bootstrap provider manifest runtime wiring', 'command' => [$php, 'tools/verify-bootstrap-provider-manifest-runtime-wiring.php']],
        ['label' => 'Verify: admin/site locale resolution', 'command' => [$php, 'tools/verify-admin-site-locale-resolution.php']],
        ['label' => 'Verify: i18n service provider registration', 'command' => [$php, 'tools/verify-i18n-service-provider-registration.php']],
        ['label' => 'Verify: module-owned translation file aggregation', 'command' => [$php, 'tools/verify-module-owned-translation-file-aggregation.php']],
        ['label' => 'Verify: admin form processor page save flow', 'command' => [$php, 'tools/verify-admin-form-processor-page-save-flow.php']],
        ['label' => 'Verify: admin form config empty handles', 'command' => [$php, 'tools/verify-admin-form-config-aggregator-empty-handles.php']],
        ['label' => 'Verify: admin form processors', 'command' => [$php, 'tools/verify-admin-form-processors.php']],
        ['label' => 'Verify: module admin form config aggregation', 'command' => [$php, 'tools/verify-module-admin-form-config-aggregation.php']],
        ['label' => 'Verify: admin form section registration', 'command' => [$php, 'tools/verify-admin-form-section-registration.php']],
        ['label' => 'Verify: admin form section registry', 'command' => [$php, 'tools/verify-admin-form-section-registry.php']],
        ['label' => 'Verify: admin page form sections', 'command' => [$php, 'tools/verify-admin-page-form-sections.php']],
        ['label' => 'Verify: Editor.js JSON save pipeline', 'command' => [$php, 'tools/verify-editorjs-json-save-pipeline.php']],
        ['label' => 'Verify: page SEO metadata', 'command' => [$php, 'tools/verify-page-seo-metadata.php']],
        ['label' => 'Verify: service providers', 'command' => [$php, 'tools/verify-service-providers.php']],
    ];
}
