<?php

declare(strict_types=1);

/**
 * Audit the first legacy verify migration pilot batch.
 *
 * This command is read-only and does not delete or rewrite source files.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

$pilotScripts = [
    'tools/verify-project-structure.php',
    'tools/verify-runtime-path-safety.php',
    'tools/verify-service-provider-manifest-file.php',
    'tools/verify-module-composer-manifests.php',
    'tools/verify-roadmap-planning-docs.php',
];

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$rows = [];
foreach ($pilotScripts as $script) {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $script);
    $exists = is_file($path);
    $contents = $exists ? (string) file_get_contents($path) : '';

    $rows[] = [
        'script' => $script,
        'exists' => $exists,
        'bytes' => $exists ? (filesize($path) ?: 0) : 0,
        'lines' => $exists ? substr_count($contents, "\n") + 1 : 0,
        'hints' => $exists ? hintSummary($contents) : ['missing'],
    ];
}

$txtPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'legacy-verify-pilot-batch-readiness.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'legacy-verify-pilot-batch-readiness.log';

$report = [];
$report[] = '# Legacy Verify Pilot Batch Readiness';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Repo root: ' . $root;
$report[] = 'Pilot scripts: ' . count($pilotScripts);
$report[] = '';
$report[] = 'This report is a read-only migration aid. It does not prove equivalent Pest coverage by itself.';
$report[] = 'Use it before deleting any legacy verify script.';

$missing = 0;
foreach ($rows as $row) {
    if (! $row['exists']) {
        $missing++;
    }

    $report[] = '';
    $report[] = '## ' . $row['script'];
    $report[] = '';
    $report[] = '- Exists: ' . ($row['exists'] ? 'yes' : 'no');
    $report[] = '- Bytes: ' . $row['bytes'];
    $report[] = '- Lines: ' . $row['lines'];
    $report[] = '- Source hints: ' . implode(', ', $row['hints']);
    $report[] = '- Migration gate: equivalent Pest coverage required before deletion';
}

file_put_contents($txtPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Legacy verify pilot batch readiness written to: ' . $txtPath;
$log[] = 'PILOT_SCRIPTS ' . count($pilotScripts);
$log[] = 'MISSING ' . $missing;
$log[] = 'REPORT_LOG ' . $logPath;

file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;

/**
 * @return list<string>
 */
function hintSummary(string $contents): array
{
    $hints = [];

    foreach ([
        'public-path' => ['public/', 'webroot', 'DOCUMENT_ROOT'],
        'runtime-path' => ['var/', 'storage', 'cache', 'runtime'],
        'services' => ['services.php', 'ServiceProvider', 'provider'],
        'composer' => ['composer.json', 'autoload', 'psr-4'],
        'docs' => ['docs/', 'roadmap', 'README'],
        'security' => ['permission', 'csrf', 'sanitize', 'path traversal'],
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
