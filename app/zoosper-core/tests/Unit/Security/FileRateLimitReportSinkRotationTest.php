<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Security;

use Zoosper\Core\Security\RateLimit\FileRateLimitReportSink;
use Zoosper\Core\Security\RateLimit\RateLimitReportEvent;

it('rotates report files when max size is reached', function (): void {
    $tempDir = sys_get_temp_dir() . '/zoosper_ratelimit_sink_' . bin2hex(random_bytes(4));
    mkdir($tempDir, 0777, true);
    $reportPath = $tempDir . '/reports.jsonl';

    try {
        $sink = new FileRateLimitReportSink($reportPath, maxSizeBytes: 100, maxFiles: 2);

        $event = new RateLimitReportEvent(
            key: 'test_key',
            identityHash: 'hash_123',
            allowed: true,
            attempts: 1,
            maxAttempts: 5,
            retryAfterSeconds: 0,
            now: time(),
        );

        $sink->record($event);
        expect(is_file($reportPath))->toBeTrue();

        $sink->record($event);
        expect(is_file($reportPath . '.1'))->toBeTrue();
    } finally {
        if (is_file($reportPath . '.2')) {
            @unlink($reportPath . '.2');
        }
        if (is_file($reportPath . '.1')) {
            @unlink($reportPath . '.1');
        }
        if (is_file($reportPath)) {
            @unlink($reportPath);
        }
        if (is_dir($tempDir)) {
            @rmdir($tempDir);
        }
    }
});

it('preserves JSON lines and the empty line produced by invalid UTF-8', function (): void {
    $tempDir = sys_get_temp_dir() . '/zoosper_ratelimit_sink_' . bin2hex(random_bytes(4));
    mkdir($tempDir, 0777, true);
    $reportPath = $tempDir . '/reports.jsonl';

    try {
        $sink = new FileRateLimitReportSink($reportPath);
        $sink->record(new RateLimitReportEvent('test/key', 'hash', true, 1, 5, 0, 123));
        $sink->record(new RateLimitReportEvent("invalid\xFF", 'hash', false, 2, 5, 3, 124));

        expect(file_get_contents($reportPath))->toBe(
            '{"key":"test/key","identity_hash":"hash","allowed":true,"attempts":1,"max_attempts":5,"retry_after_seconds":0,"now":123}' . PHP_EOL . PHP_EOL,
        );
    } finally {
        if (is_file($reportPath)) {
            unlink($reportPath);
        }
        rmdir($tempDir);
    }
});










