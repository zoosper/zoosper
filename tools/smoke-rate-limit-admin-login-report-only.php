<?php

declare(strict_types=1);

/**
 * End-to-end report-only smoke for the admin.login rate-limit policy.
 */

$root = dirname(__DIR__);
$outputDir = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'reports';
$databasePath = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-smoke.sqlite';
$now = 300;

foreach ($argv as $argument) {
    if (str_starts_with($argument, '--output-dir=')) {
        $outputDir = substr($argument, strlen('--output-dir='));
        continue;
    }
    if (str_starts_with($argument, '--database=')) {
        $databasePath = substr($argument, strlen('--database='));
        continue;
    }
    if (str_starts_with($argument, '--now=')) {
        $now = (int) substr($argument, strlen('--now='));
    }
}

if (! is_dir($outputDir) && ! mkdir($outputDir, 0775, true) && ! is_dir($outputDir)) {
    fwrite(STDERR, 'Unable to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

if (! is_dir(dirname($databasePath)) && ! mkdir(dirname($databasePath), 0775, true) && ! is_dir(dirname($databasePath))) {
    fwrite(STDERR, 'Unable to create database directory: ' . dirname($databasePath) . PHP_EOL);
    exit(1);
}

foreach ([
    'RateLimitDecision.php',
    'RateLimitRule.php',
    'RateLimitStoreInterface.php',
    'DatabaseRateLimitStore.php',
    'RateLimitPolicy.php',
    'RateLimitPolicyResolverInterface.php',
    'StaticRateLimitPolicyResolver.php',
    'RateLimitEnforcer.php',
    'RateLimitContext.php',
    'RateLimitIdentityHasher.php',
    'RateLimitGuard.php',
    'RateLimitReportEvent.php',
    'RateLimitReportSinkInterface.php',
    'FileRateLimitReportSink.php',
    'ReportOnlyRateLimitMiddleware.php',
    'RateLimitRuntimeConfig.php',
    'RateLimitReportOnlyMiddlewareFactory.php',
    'RateLimitMiddlewareIntegration.php',
    'AdminRateLimitContextFactory.php',
] as $file) {
    require_once $root . '/app/zoosper-core/src/Security/RateLimit/' . $file;
}

use Zoosper\Core\Security\RateLimit\AdminRateLimitContextFactory;
use Zoosper\Core\Security\RateLimit\DatabaseRateLimitStore;
use Zoosper\Core\Security\RateLimit\FileRateLimitReportSink;
use Zoosper\Core\Security\RateLimit\RateLimitIdentityHasher;
use Zoosper\Core\Security\RateLimit\RateLimitMiddlewareIntegration;
use Zoosper\Core\Security\RateLimit\RateLimitReportOnlyMiddlewareFactory;
use Zoosper\Core\Security\RateLimit\RateLimitRuntimeConfig;

$configPath = $root . '/app/zoosper-core/config/rate_limit.php';
$config = is_file($configPath) ? require $configPath : null;
$errors = [];

if (! is_array($config)) {
    $errors[] = 'rate_limit.php did not return an array.';
    $config = [];
}

if (($config['enabled'] ?? null) !== false) {
    $errors[] = 'rate_limit.php must remain disabled by default for this smoke.';
}

if (($config['mode'] ?? null) !== 'report_only') {
    $errors[] = 'rate_limit.php must default to report_only mode for this smoke.';
}

if (! isset(($config['policies'] ?? [])['admin.login'])) {
    $errors[] = 'admin.login policy is missing. Run tools/apply-rate-limit-admin-login-policy.php --apply first.';
}

$reportPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-smoke.txt';
$logPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-smoke.log';
$eventsPath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'rate-limit-admin-login-smoke-events.jsonl';

$downstreamRuns = 0;
$deniedSeen = false;
$eventCount = 0;
$attemptsRun = 0;

if ($errors === []) {
    @unlink($databasePath);
    @unlink($eventsPath);

    $runtimeConfigArray = $config;
    $runtimeConfigArray['enabled'] = true;
    $runtimeConfigArray['mode'] = 'report_only';
    $runtimeConfigArray['report_path'] = $eventsPath;

    $runtimeConfig = RateLimitRuntimeConfig::fromArray($runtimeConfigArray);
    $pdo = new PDO('sqlite:' . $databasePath);
    $store = new DatabaseRateLimitStore($pdo);
    $store->ensureSchema();

    $middlewareList = (new RateLimitMiddlewareIntegration(
        new RateLimitReportOnlyMiddlewareFactory($store, new FileRateLimitReportSink($eventsPath)),
    ))->middleware($runtimeConfig);

    if (count($middlewareList) !== 1) {
        $errors[] = 'Expected exactly one report-only middleware from enabled runtime config.';
    } else {
        $factory = new AdminRateLimitContextFactory(new RateLimitIdentityHasher(), $runtimeConfig);
        $maxAttempts = $runtimeConfig->policies['admin.login']->maxAttempts;
        $attemptsRun = $maxAttempts + 1;

        for ($i = 0; $i < $attemptsRun; $i++) {
            $context = $factory->create('admin.login', ['admin-smoke@example.test', '127.0.0.1'], $now + $i);
            $middlewareList[0]->handle($context, function ($decision) use (&$downstreamRuns, &$deniedSeen): string {
                $downstreamRuns++;
                if (! $decision->allowed) {
                    $deniedSeen = true;
                }
                return 'downstream-ran';
            });
        }

        if (is_file($eventsPath)) {
            $lines = array_filter(explode(PHP_EOL, trim((string) file_get_contents($eventsPath))));
            $eventCount = count($lines);
        }

        if (! $deniedSeen) {
            $errors[] = 'Expected at least one denied decision in report-only smoke.';
        }

        if ($downstreamRuns !== $attemptsRun) {
            $errors[] = 'Expected downstream to run for every attempt.';
        }

        if ($eventCount !== $attemptsRun) {
            $errors[] = 'Expected one JSONL report event per attempt.';
        }
    }
}

$report = [];
$report[] = '# Admin Login Report-only Rate Limit Smoke';
$report[] = '';
$report[] = 'Generated: ' . (new DateTimeImmutable('now'))->format(DateTimeInterface::ATOM);
$report[] = 'Errors: ' . count($errors);
$report[] = 'Attempts run: ' . $attemptsRun;
$report[] = 'Downstream runs: ' . $downstreamRuns;
$report[] = 'Denied seen: ' . ($deniedSeen ? 'yes' : 'no');
$report[] = 'Report events: ' . $eventCount;
$report[] = 'Database: ' . $databasePath;
$report[] = 'Events: ' . $eventsPath;

if ($errors !== []) {
    $report[] = '';
    $report[] = '## Errors';
    foreach ($errors as $error) {
        $report[] = '- ' . $error;
    }
}

file_put_contents($reportPath, implode(PHP_EOL, $report) . PHP_EOL);

$log = [];
$log[] = 'Admin login rate-limit report-only smoke written to: ' . $reportPath;
$log[] = 'ADMIN_LOGIN_SMOKE_ERRORS ' . count($errors);
$log[] = 'ADMIN_LOGIN_SMOKE_ATTEMPTS ' . $attemptsRun;
$log[] = 'ADMIN_LOGIN_SMOKE_DOWNSTREAM_RUNS ' . ($downstreamRuns === $attemptsRun && $attemptsRun > 0 ? 'yes' : 'no');
$log[] = 'ADMIN_LOGIN_SMOKE_DENIED_SEEN ' . ($deniedSeen ? 'yes' : 'no');
$log[] = 'ADMIN_LOGIN_SMOKE_REPORT_EVENTS ' . $eventCount;
$log[] = 'EVENTS_PATH ' . $eventsPath;
$log[] = 'REPORT_LOG ' . $logPath;
file_put_contents($logPath, implode(PHP_EOL, $log) . PHP_EOL);

echo implode(PHP_EOL, $log) . PHP_EOL;
exit($errors === [] ? 0 : 1);
