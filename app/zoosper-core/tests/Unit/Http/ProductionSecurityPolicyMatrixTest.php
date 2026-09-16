<?php

declare(strict_types=1);

use Zoosper\Core\Http\ProductionSecurityPolicy;

function setProductionPolicyEnvironment(array $values): void
{
    foreach (['APP_ENV', 'APP_DEBUG', 'SESSION_SECURE', 'RATE_LIMIT_ENABLED', 'RATE_LIMIT_MODE', 'RATE_LIMIT_IDENTITY_SALT', 'TWO_FACTOR_ENCRYPTION_KEY', 'APP_KEY', 'DB_DRIVER', 'DB_CONNECTION', 'DATABASE_ENFORCE_MYSQL_PRODUCTION', 'CACHE_DRIVER', 'CACHE_REDIS_PASSWORD', 'CACHE_ENCRYPTION_KEY'] as $key) {
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

it('accepts authenticated Redis with a strong signing key in public environments', function (string $environment): void {
    setProductionPolicyEnvironment([
        'APP_ENV' => $environment,
        'APP_DEBUG' => 'false',
        'SESSION_SECURE' => 'true',
        'RATE_LIMIT_ENABLED' => 'true',
        'RATE_LIMIT_MODE' => 'enforce',
        'RATE_LIMIT_IDENTITY_SALT' => str_repeat('a', 64),
        'TWO_FACTOR_ENCRYPTION_KEY' => str_repeat('b', 64),
        'APP_KEY' => str_repeat('c', 64),
        'DB_CONNECTION' => 'mysql',
        'DATABASE_ENFORCE_MYSQL_PRODUCTION' => 'true',
        'CACHE_DRIVER' => 'redis',
        'CACHE_REDIS_PASSWORD' => str_repeat('d', 64),
        'CACHE_ENCRYPTION_KEY' => str_repeat('e', 64),
    ]);

    expect(fn () => ProductionSecurityPolicy::assertEnvironment())
        ->not->toThrow(RuntimeException::class);
})->with(['staging', 'production']);

it('rejects each insecure public Redis control without exposing configured secrets', function (
    string $key,
    string $value,
    string $expectedMessage,
): void {
    $redisPassword = 'redis-password-must-not-appear-in-errors';
    $signingKey = 'cache-signing-key-must-not-appear-in-errors';

    setProductionPolicyEnvironment([
        'APP_ENV' => 'production',
        'APP_DEBUG' => 'false',
        'SESSION_SECURE' => 'true',
        'RATE_LIMIT_ENABLED' => 'true',
        'RATE_LIMIT_MODE' => 'enforce',
        'RATE_LIMIT_IDENTITY_SALT' => str_repeat('a', 64),
        'TWO_FACTOR_ENCRYPTION_KEY' => str_repeat('b', 64),
        'APP_KEY' => str_repeat('c', 64),
        'DB_CONNECTION' => 'mysql',
        'DATABASE_ENFORCE_MYSQL_PRODUCTION' => 'true',
        'CACHE_DRIVER' => 'redis',
        'CACHE_REDIS_PASSWORD' => $redisPassword,
        'CACHE_ENCRYPTION_KEY' => $signingKey,
        $key => $value,
    ]);

    try {
        ProductionSecurityPolicy::assertEnvironment();
        throw new LogicException('Expected the Redis production policy to reject the configuration.');
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())
            ->toContain($expectedMessage)
            ->not->toContain($redisPassword)
            ->not->toContain($signingKey);
    }
})->with([
    'empty Redis password' => [
        'CACHE_REDIS_PASSWORD',
        '',
        'requires authenticated Redis when CACHE_DRIVER=redis',
    ],
    'placeholder Redis password' => [
        'CACHE_REDIS_PASSWORD',
        'change-me',
        'requires authenticated Redis when CACHE_DRIVER=redis',
    ],
    'empty cache signing key' => [
        'CACHE_ENCRYPTION_KEY',
        '',
        'requires a strong CACHE_ENCRYPTION_KEY when CACHE_DRIVER=redis',
    ],
    'short cache signing key' => [
        'CACHE_ENCRYPTION_KEY',
        'short-cache-key',
        'requires a strong CACHE_ENCRYPTION_KEY when CACHE_DRIVER=redis',
    ],
    'placeholder cache signing key' => [
        'CACHE_ENCRYPTION_KEY',
        'placeholder',
        'requires a strong CACHE_ENCRYPTION_KEY when CACHE_DRIVER=redis',
    ],
]);

it('does not impose Redis controls when the file cache driver is selected', function (
    string $environment,
): void {
    setProductionPolicyEnvironment([
        'APP_ENV' => $environment,
        'APP_DEBUG' => 'false',
        'SESSION_SECURE' => 'true',
        'RATE_LIMIT_ENABLED' => 'true',
        'RATE_LIMIT_MODE' => 'enforce',
        'RATE_LIMIT_IDENTITY_SALT' => str_repeat('a', 64),
        'TWO_FACTOR_ENCRYPTION_KEY' => str_repeat('b', 64),
        'APP_KEY' => str_repeat('c', 64),
        'DB_CONNECTION' => 'mysql',
        'DATABASE_ENFORCE_MYSQL_PRODUCTION' => 'true',
        'CACHE_DRIVER' => 'file',
        'CACHE_REDIS_PASSWORD' => '',
        'CACHE_ENCRYPTION_KEY' => '',
    ]);

    expect(fn () => ProductionSecurityPolicy::assertEnvironment())
        ->not->toThrow(RuntimeException::class);
})->with(['staging', 'production']);

it('retains explicit practical Redis behaviour outside public environments', function (
    string $environment,
): void {
    setProductionPolicyEnvironment([
        'APP_ENV' => $environment,
        'CACHE_DRIVER' => 'redis',
        'CACHE_REDIS_PASSWORD' => '',
        'CACHE_ENCRYPTION_KEY' => '',
    ]);

    expect(fn () => ProductionSecurityPolicy::assertEnvironment())
        ->not->toThrow(RuntimeException::class);
})->with(['local', 'development', 'testing']);
