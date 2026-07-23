<?php

declare(strict_types=1);

/**
 * Read-only first migration planner for config layering.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$candidates = [];
foreach (['app', 'packages'] as $base) {
    $dir = $root . DIRECTORY_SEPARATOR . $base;
    if (! is_dir($dir)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (! $file->isFile() || strtolower($file->getExtension()) !== 'php') {
            continue;
        }
        $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
        if (! str_contains($relative, '/config/')) {
            continue;
        }
        $score = scoreCandidate($relative, (string) file_get_contents($file->getPathname()));
        $candidates[$relative] = $score;
    }
}
uksort($candidates, static fn (string $a, string $b): int => ($candidates[$b]['score'] <=> $candidates[$a]['score']) ?: strcmp($a, $b));

$top = array_slice($candidates, 0, 15, true);
$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config-layering-first-migration-plan.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'config-layering-first-migration-plan.log';

$report = [];
$report[] = '# Config Layering First Migration Plan';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Candidates scored: ' . count($candidates);
$report[] = '';
$report[] = '## Top candidates';
foreach ($top as $relative => $score) {
    $report[] = '';
    $report[] = '### ' . $relative;
    $report[] = '- score: ' . $score['score'];
    foreach ($score['reasons'] as $reason) {
        $report[] = '- ' . $reason;
    }
}
$report[] = '';
$report[] = '## Migration recommendation';
$report[] = 'Pick one low-risk, associative-array-heavy config type after reviewing this report. Avoid routes, middleware, services, and auth-sensitive config as first migration targets.';

file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Config layering first migration plan written to: ' . $reportPath;
$log[] = 'CONFIG_LAYERING_MIGRATION_CANDIDATES ' . count($candidates);
$log[] = 'CONFIG_LAYERING_FIRST_MIGRATION_PLAN_ERRORS 0';
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit(0);

/** @return array{score:int,reasons:list<string>} */
function scoreCandidate(string $relative, string $source): array
{
    $score = 0;
    $reasons = [];
    if (preg_match('/return\s*\[/', $source) === 1) {
        $score += 2;
        $reasons[] = 'simple return-array config';
    }
    foreach (['route', 'middleware', 'services', 'auth', 'csrf'] as $risk) {
        if (stripos($relative, $risk) !== false || stripos($source, $risk) !== false) {
            $score -= 4;
            $reasons[] = 'high-risk signal: ' . $risk;
        }
    }
    foreach (['menu', 'form', 'events', 'schema', 'locale', 'settings'] as $safeish) {
        if (stripos($relative, $safeish) !== false || stripos($source, $safeish) !== false) {
            $score += 1;
            $reasons[] = 'possible lower-risk signal: ' . $safeish;
        }
    }
    if (str_contains($source, '=> [')) {
        $score += 1;
        $reasons[] = 'contains nested associative-looking arrays';
    }
    return ['score' => $score, 'reasons' => $reasons === [] ? ['no strong signal'] : $reasons];
}
