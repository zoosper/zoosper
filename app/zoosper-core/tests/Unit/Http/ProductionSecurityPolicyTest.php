<?php

declare(strict_types=1);
use Zoosper\Core\Http\ProductionSecurityPolicy;
it('fails closed for insecure production defaults', function (string $envVar, string $value, string $expectedError): void {
    // Reset to safe baseline
    putenv('APP_ENV=production');
    putenv('APP_DEBUG=false');
    putenv('SESSION_SECURE=true');
    putenv('RATE_LIMIT_ENABLED=true');
    putenv('RATE_LIMIT_MODE=enforce');
    putenv('RATE_LIMIT_IDENTITY_SALT=strong-random-test-salt');
    putenv('TWO_FACTOR_ENCRYPTION_KEY=strong-random-2fa-test-key');
    putenv('DB_DRIVER=mysql');

    // Trigger failure
    putenv($envVar . '=' . $value);

    expect(fn () => ProductionSecurityPolicy::assertEnvironment())
        ->toThrow(RuntimeException::class, $expectedError);

    // Cleanup
    putenv('APP_ENV=local');
})->with([
    ['APP_DEBUG', 'true', 'Production requires APP_DEBUG=false.'],
    ['SESSION_SECURE', 'false', 'Production requires SESSION_SECURE=true.'],
    ['RATE_LIMIT_ENABLED', 'false', 'Production requires RATE_LIMIT_ENABLED=true.'],
    ['RATE_LIMIT_MODE', 'report_only', 'Production requires RATE_LIMIT_MODE=enforce.'],
    ['RATE_LIMIT_IDENTITY_SALT', 'change-me', 'Production requires a strong RATE_LIMIT_IDENTITY_SALT.'],
    ['TWO_FACTOR_ENCRYPTION_KEY', 'secret', 'Production requires a strong TWO_FACTOR_ENCRYPTION_KEY.'],
    ['DB_DRIVER', 'sqlite', 'Production requires a production database driver'],
]);

it('passes for secure production environment', function (): void {
    putenv('APP_ENV=production');
    putenv('APP_DEBUG=false');
    putenv('SESSION_SECURE=true');
    putenv('RATE_LIMIT_ENABLED=true');
    putenv('RATE_LIMIT_MODE=enforce');
    putenv('RATE_LIMIT_IDENTITY_SALT=d8a39e87b64921f045c36190ab74e1d3e89a54f2c019d678b4a2e5c7f8901234');
    putenv('TWO_FACTOR_ENCRYPTION_KEY=d8a39e87b64921f045c36190ab74e1d3e89a54f2c019d678b4a2e5c7f8901234');
    putenv('DB_CONNECTION=mysql');
    putenv('DB_DRIVER=mysql');

    expect(fn () => ProductionSecurityPolicy::assertEnvironment())->not->toThrow(RuntimeException::class);

    putenv('APP_ENV=local');
    putenv('DB_CONNECTION');
});










