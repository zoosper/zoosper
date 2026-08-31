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










