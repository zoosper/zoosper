<?php

declare(strict_types=1);
use Zoosper\Core\Http\ProductionSecurityPolicy;
it('fails closed for insecure production defaults', function (): void {
    putenv('APP_ENV=production');
    putenv('APP_DEBUG=false');
    putenv('SESSION_SECURE=false');
    putenv('RATE_LIMIT_ENABLED=true');
    putenv('RATE_LIMIT_MODE=enforce');
    putenv('RATE_LIMIT_IDENTITY_SALT=strong-random-test-salt');
    putenv('TWO_FACTOR_ENCRYPTION_KEY=strong-random-2fa-test-key');
    putenv('DB_DRIVER=mysql');
    expect(fn () => ProductionSecurityPolicy::assertEnvironment())->toThrow(RuntimeException::class);
    putenv('APP_ENV=local');
});
