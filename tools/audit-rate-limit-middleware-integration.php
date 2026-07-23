<?php

declare(strict_types=1);

/**
 * Read-only audit for rate limit middleware integration readiness.
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

$required = [
    'docs/development/rate-limit-middleware-integration-readiness.md',
    'tools/audit-rate-limit-middleware-integration.php',
    'app/zoosper-core/src/Security/RateLimit/ReportOnlyRateLimitMiddleware.php',
    'app/zoosper-core/src/Security/RateLimit/RateLimitGuard.php',
];

$errors = [];
foreach ($required as $relative) {
    if (! is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative))) {
        $errors[] = 'Required file missing: ' . $relative;
    }
}

$candidates = scanCandidates($root);
$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-middleware-integration.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-middleware-integration.log';

$report = [];
$report[] = '# Rate Limit Middleware Integration Readiness Audit';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Candidate files: ' . count($candidates);
$report[] = '';
$report[] = '## Required files';
foreach ($required as $relative) {
    $report[] = '- ' . $relative . ': ' . (is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative)) ? 'exists' : 'missing');
}
$report[] = '';
$report[] = '## Candidate integration files';
if ($candidates === []) {
    $report[] = '- none found';
} else {
    foreach ($candidates as $relative => $signals) {
        $report[] = '';
        $report[] = '### ' . $relative;
        foreach ($signals as $signal) {
            $report[] = '- ' . $signal;
        }
    }
}

$report[] = '';
$report[] = '## Interpretation';
$report[] = 'This audit is read-only. Candidate files identify where the next phase may wire report-only rate limiting behind an explicit disabled-by-default switch.';

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Rate limit middleware integration audit written to: ' . $reportPath;
$log[] = 'RATE_LIMIT_MIDDLEWARE_INTEGRATION_ERRORS ' . count($errors);
$log[] = 'CANDIDATE_INTEGRATION_FILES ' . count($candidates);
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);

/** @return array<string,list<string>> */
function scanCandidates(string $root): array
{
    $bases = ['app', 'packages'];
    $needles = [
        'middleware',
        'pipeline',
        'ModuleRouteDefinition',
        'AuthenticationMiddleware',
        'Csrf',
        'routes.php',
        'config/middleware',
    ];

    $candidates = [];
    foreach ($bases as $base) {
        $directory = $root . DIRECTORY_SEPARATOR . $base;
        if (! is_dir($directory)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $extension = strtolower($file->getExtension());
            if (! in_array($extension, ['php', 'md'], true)) {
                continue;
            }

            $relative = str_replace($root . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $contents = (string) file_get_contents($file->getPathname());
            $signals = [];

            foreach ($needles as $needle) {
                if (stripos($relative, $needle) !== false || stripos($contents, $needle) !== false) {
                    $signals[] = 'contains ' . $needle;
                }
            }

            if ($signals !== []) {
                $candidates[$relative] = array_values(array_unique($signals));
            }
        }
    }

    ksort($candidates);
    return $candidates;
}
