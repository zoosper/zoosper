<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\DatabaseRateLimitStore;

require_once dirname(__DIR__) . '/app/zoosper-core/src/Security/RateLimit/RateLimitDecision.php';
require_once dirname(__DIR__) . '/app/zoosper-core/src/Security/RateLimit/RateLimitRule.php';
require_once dirname(__DIR__) . '/app/zoosper-core/src/Security/RateLimit/RateLimitStoreInterface.php';
require_once dirname(__DIR__) . '/app/zoosper-core/src/Security/RateLimit/DatabaseRateLimitStore.php';

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
$databasePath = null;
$apply = false;
$now = time();

foreach ($argv as $argument) {
    if ($argument === '--apply') {
        $apply = true;
        continue;
    }

    if (str_starts_with($argument, '--database=')) {
        $databasePath = substr($argument, strlen('--database='));
        continue;
    }

    if (str_starts_with($argument, '--now=')) {
        $now = (int) substr($argument, strlen('--now='));
        continue;
    }

    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
    }
}

if ($databasePath === null || $databasePath === '') {
    fwrite(STDERR, "Missing required --database=<sqlite path> option.\n");
    exit(1);
}

if (! is_dir(dirname($databasePath)) && ! mkdir(dirname($databasePath), 0775, true) && ! is_dir(dirname($databasePath))) {
    fwrite(STDERR, 'Unable to create database directory: ' . dirname($databasePath) . PHP_EOL);
    exit(1);
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$pdo = new PDO('sqlite:' . $databasePath);
$store = new DatabaseRateLimitStore($pdo);
$store->ensureSchema();

$countStatement = $pdo->prepare('SELECT COUNT(*) FROM rate_limit_buckets WHERE window_ends_at <= :now');
$countStatement->execute([':now' => $now]);
$expiredCount = (int) $countStatement->fetchColumn();
$deletedCount = 0;

if ($apply) {
    $deletedCount = $store->deleteExpired($now);
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-cleanup.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-cleanup.log';

$report = [];
$report[] = '# Rate Limit Cleanup Report';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Mode: ' . ($apply ? 'apply' : 'dry-run');
$report[] = 'Database: ' . $databasePath;
$report[] = 'Now: ' . $now;
$report[] = 'Expired buckets: ' . $expiredCount;
$report[] = 'Deleted buckets: ' . $deletedCount;
file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Rate limit cleanup report written to: ' . $reportPath;
$log[] = 'MODE ' . ($apply ? 'apply' : 'dry-run');
$log[] = 'EXPIRED_BUCKETS ' . $expiredCount;
$log[] = 'DELETED_BUCKETS ' . $deletedCount;
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit(0);
