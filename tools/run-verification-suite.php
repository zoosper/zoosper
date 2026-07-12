<?php

declare(strict_types=1);

/**
 * Runs Zoosper verification commands and writes full output to a report file.
 *
 * Usage:
 *   php tools/run-verification-suite.php
 *   php tools/run-verification-suite.php --output=var/reports/my-report.txt
 */

$rootPath = dirname(__DIR__);
$outputPath = zoosper_verification_output_path($rootPath, $argv);
$startedAt = new DateTimeImmutable('now');
$commands = zoosper_verification_commands();
$results = [];
$failed = false;

foreach ($commands as $entry) {
    $result = zoosper_run_command($entry['command'], $rootPath);
    $results[] = [
        'label' => $entry['label'],
        'command' => $entry['command'],
        'exitCode' => $result['exitCode'],
        'stdout' => $result['stdout'],
        'stderr' => $result['stderr'],
    ];

    if ($result['exitCode'] !== 0) {
        $failed = true;
    }
}

$finishedAt = new DateTimeImmutable('now');
zoosper_write_report($outputPath, $startedAt, $finishedAt, $results, $failed);
zoosper_print_summary($outputPath, $results, $failed);

exit($failed ? 2 : 0);

/** @return list<array{label: string, command: list<string>}> */
function zoosper_verification_commands(): array
{
    $php = PHP_BINARY;

    return [
        ['label' => 'Syntax: config/i18n.php', 'command' => [$php, '-l', 'config/i18n.php']],
        ['label' => 'Syntax: LocaleResolution.php', 'command' => [$php, '-l', 'app/zoosper-core/src/I18n/LocaleResolution.php']],
        ['label' => 'Syntax: LocaleResolverInterface.php', 'command' => [$php, '-l', 'app/zoosper-core/src/I18n/LocaleResolverInterface.php']],
        ['label' => 'Syntax: ConfiguredLocaleResolver.php', 'command' => [$php, '-l', 'app/zoosper-core/src/I18n/ConfiguredLocaleResolver.php']],
        ['label' => 'Syntax: verify-admin-site-locale-resolution.php', 'command' => [$php, '-l', 'tools/verify-admin-site-locale-resolution.php']],
        ['label' => 'Syntax: run-verification-suite.php', 'command' => [$php, '-l', 'tools/run-verification-suite.php']],
        ['label' => 'Verify: admin/site locale resolution', 'command' => [$php, 'tools/verify-admin-site-locale-resolution.php']],
        ['label' => 'Verify: translatable admin system messages', 'command' => [$php, 'tools/verify-translatable-admin-system-messages.php']],
        ['label' => 'Verify: admin translator resolution', 'command' => [$php, 'tools/verify-admin-translator-resolution.php']],
        ['label' => 'Verify: translation file aggregator comment safety', 'command' => [$php, 'tools/verify-translation-file-aggregator-comment-safety.php']],
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

/** @param list<string> $argv */
function zoosper_verification_output_path(string $rootPath, array $argv): string
{
    foreach ($argv as $argument) {
        if (str_starts_with($argument, '--output=')) {
            $path = trim(substr($argument, strlen('--output=')));
            if ($path !== '') {
                return str_starts_with($path, '/') ? $path : $rootPath . '/' . $path;
            }
        }
    }

    $timestamp = (new DateTimeImmutable('now'))->format('Ymd-His');

    return $rootPath . '/var/reports/verification-' . $timestamp . '.txt';
}

/**
 * @param list<string> $command
 *
 * @return array{exitCode: int, stdout: string, stderr: string}
 */
function zoosper_run_command(array $command, string $rootPath): array
{
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $commandString = implode(' ', array_map('escapeshellarg', $command));
    $process = proc_open($commandString, $descriptorSpec, $pipes, $rootPath);

    if (!is_resource($process)) {
        return [
            'exitCode' => 127,
            'stdout' => '',
            'stderr' => 'Unable to start command: ' . $commandString,
        ];
    }

    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]) ?: '';
    $stderr = stream_get_contents($pipes[2]) ?: '';
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    return [
        'exitCode' => is_int($exitCode) ? $exitCode : 1,
        'stdout' => $stdout,
        'stderr' => $stderr,
    ];
}

/**
 * @param list<array{label: string, command: list<string>, exitCode: int, stdout: string, stderr: string}> $results
 */
function zoosper_write_report(
    string $outputPath,
    DateTimeImmutable $startedAt,
    DateTimeImmutable $finishedAt,
    array $results,
    bool $failed,
): void {
    $directory = dirname($outputPath);
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $lines = [];
    $lines[] = 'Zoosper verification suite report';
    $lines[] = '=================================';
    $lines[] = 'Started: ' . $startedAt->format(DATE_ATOM);
    $lines[] = 'Finished: ' . $finishedAt->format(DATE_ATOM);
    $lines[] = 'Overall result: ' . ($failed ? 'FAIL' : 'OK');
    $lines[] = '';

    foreach ($results as $index => $result) {
        $lines[] = sprintf('[%02d] %s', $index + 1, $result['label']);
        $lines[] = 'Command: ' . implode(' ', $result['command']);
        $lines[] = 'Exit code: ' . $result['exitCode'];
        $lines[] = '--- STDOUT ---';
        $lines[] = rtrim($result['stdout']);
        $lines[] = '--- STDERR ---';
        $lines[] = rtrim($result['stderr']);
        $lines[] = str_repeat('-', 80);
    }

    file_put_contents($outputPath, implode(PHP_EOL, $lines) . PHP_EOL);
}

/**
 * @param list<array{label: string, command: list<string>, exitCode: int, stdout: string, stderr: string}> $results
 */
function zoosper_print_summary(string $outputPath, array $results, bool $failed): void
{
    print 'Zoosper verification suite' . PHP_EOL;
    print '==========================' . PHP_EOL;
    print 'Report: ' . $outputPath . PHP_EOL;
    print 'Overall result: ' . ($failed ? 'FAIL' : 'OK') . PHP_EOL . PHP_EOL;

    foreach ($results as $result) {
        print '- ' . $result['label'] . ': ' . ($result['exitCode'] === 0 ? 'ok' : 'FAIL') . PHP_EOL;
    }
}
