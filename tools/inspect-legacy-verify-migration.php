<?php

declare(strict_types=1);

/**
 * Inspect legacy tools/verify-*.php scripts and write a migration report.
 *
 * This command is intentionally read-only. It does not delete or rewrite files.
 */

$root = dirname(__DIR__);
$toolsDir = $root . DIRECTORY_SEPARATOR . 'tools';
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($toolsDir)) {
    fwrite(STDERR, "Tools directory not found: {$toolsDir}" . PHP_EOL);
    exit(1);
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, "Unable to create report directory: {$outputDir}" . PHP_EOL);
    exit(1);
}

$scripts = glob($toolsDir . DIRECTORY_SEPARATOR . 'verify-*.php') ?: [];
sort($scripts);

$rows = [];
foreach ($scripts as $script) {
    $relative = 'tools/' . basename($script);
    $contents = (string) file_get_contents($script);
    $rows[] = [
        'path' => $relative,
        'bytes' => filesize($script) ?: 0,
        'lines' => substr_count($contents, "\n") + 1,
        'hints' => migrationHints($contents),
    ];
}

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'legacy-verify-migration-inspection.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'legacy-verify-migration-inspection.log';

$report = [];
$report[] = '# Legacy Verify Migration Inspection';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Legacy verify scripts: ' . count($rows);
$report[] = '';
$report[] = 'This report is generated from filenames and source hints to support Pest migration planning.';
$report[] = 'It is a runtime artefact and should not normally be committed.';

foreach ($rows as $row) {
    $report[] = '';
    $report[] = '## ' . $row['path'];
    $report[] = '';
    $report[] = '- Bytes: ' . $row['bytes'];
    $report[] = '- Lines: ' . $row['lines'];
    $report[] = '- Migration hints: ' . implode(', ', $row['hints']);
}

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Legacy verify migration inspection written to: ' . $txtPath;
$log[] = 'LEGACY_VERIFY_SCRIPTS ' . count($rows);
$log[] = 'REPORT_LOG ' . $logPath;

file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

/**
 * @return list<string>
 */
function migrationHints(string $contents): array
{
    $hints = [];

    foreach ([
        'schema' => ['schema', 'db_schema'],
        'routes' => ['routes.php', 'route'],
        'services' => ['services.php', 'ServiceProvider', 'service'],
        'composer' => ['composer.json', 'autoload', 'psr-4'],
        'docs' => ['docs/', 'roadmap', 'README'],
        'security' => ['csrf', 'sanitize', 'permission', 'public'],
        'templates' => ['Latte', 'template', 'views'],
    ] as $hint => $needles) {
        foreach ($needles as $needle) {
            if (stripos($contents, $needle) !== false) {
                $hints[] = $hint;
                break;
            }
        }
    }

    return $hints === [] ? ['source-contract'] : array_values(array_unique($hints));
}
