<?php

declare(strict_types=1);

use Zoosper\Core\Http\ProductionSecurityPolicy;

function setProductionPolicyEnvironment(array $values): void
{
    foreach (['APP_ENV', 'APP_DEBUG', 'SESSION_SECURE', 'RATE_LIMIT_ENABLED', 'RATE_LIMIT_MODE', 'RATE_LIMIT_IDENTITY_SALT', 'TWO_FACTOR_ENCRYPTION_KEY', 'APP_KEY', 'DB_DRIVER', 'DB_CONNECTION', 'DATABASE_ENFORCE_MYSQL_PRODUCTION'] as $key) {
        unset($_ENV[$key]);
        putenv($key);
    }

    foreach ($values as $key => $value) {
        $_ENV[$key] = $value;
        putenv($key . '=' . $value);
    }
}

afterEach(function (): void {
    setProductionPolicyEnvironment(['APP_ENV' => 'testing']);
});

it('accepts complete fail-closed staging and production controls', function (): void {
    foreach (['staging', 'production'] as $environment) {
        setProductionPolicyEnvironment([
            'APP_ENV' => $environment,
            'APP_DEBUG' => 'false',
            'SESSION_SECURE' => 'true',
            'RATE_LIMIT_ENABLED' => 'true',
            'RATE_LIMIT_MODE' => 'enforce',
            'RATE_LIMIT_IDENTITY_SALT' => str_repeat('a', 64),
            'TWO_FACTOR_ENCRYPTION_KEY' => str_repeat('c', 64),
            'APP_KEY' => str_repeat('e', 64),
            'DB_DRIVER' => 'mysql',
            'DATABASE_ENFORCE_MYSQL_PRODUCTION' => 'true',
        ]);

        ProductionSecurityPolicy::assertEnvironment();
        expect(true)->toBeTrue();
    }
});

it('rejects each weakened public-environment control independently', function (string $key, string $value): void {
    setProductionPolicyEnvironment([
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'SESSION_SECURE' => 'true',
        'RATE_LIMIT_ENABLED' => 'true',
        'RATE_LIMIT_MODE' => 'enforce',
        'RATE_LIMIT_IDENTITY_SALT' => str_repeat('b', 64),
        'TWO_FACTOR_ENCRYPTION_KEY' => str_repeat('d', 64),
        'APP_KEY' => str_repeat('f', 64),
        'DB_DRIVER' => 'mysql',
        'DATABASE_ENFORCE_MYSQL_PRODUCTION' => 'true',
        $key => $value,
    ]);

    expect(fn () => ProductionSecurityPolicy::assertEnvironment())
        ->toThrow(\RuntimeException::class);
})->with([
    'debug enabled' => ['APP_DEBUG', 'true'],
    'insecure session' => ['SESSION_SECURE', 'false'],
    'disabled rate limiting' => ['RATE_LIMIT_ENABLED', 'false'],
    'report-only rate limiting' => ['RATE_LIMIT_MODE', 'report_only'],
    'placeholder identity salt' => ['RATE_LIMIT_IDENTITY_SALT', 'change-me'],
    'placeholder 2fa key' => ['TWO_FACTOR_ENCRYPTION_KEY', 'change-me'],
    'empty 2fa key' => ['TWO_FACTOR_ENCRYPTION_KEY', ''],
    'placeholder app key' => ['APP_KEY', 'change-me'],
    'sqlite in production' => ['DB_DRIVER', 'sqlite'],
]);










