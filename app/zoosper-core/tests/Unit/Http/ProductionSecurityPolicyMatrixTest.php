<?php

declare(strict_types=1);

use Zoosper\Core\Http\ProductionSecurityPolicy;

function setProductionPolicyEnvironment(array $values): void
{
    foreach (['APP_ENV', 'SESSION_SECURE', 'RATE_LIMIT_ENABLED', 'RATE_LIMIT_MODE', 'RATE_LIMIT_IDENTITY_SALT'] as $key) {
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
            'SESSION_SECURE' => 'true',
            'RATE_LIMIT_ENABLED' => 'true',
            'RATE_LIMIT_MODE' => 'enforce',
            'RATE_LIMIT_IDENTITY_SALT' => str_repeat('a', 64),
        ]);

        ProductionSecurityPolicy::assertEnvironment();
        expect(true)->toBeTrue();
    }
});

it('rejects each weakened public-environment control independently', function (string $key, string $value): void {
    setProductionPolicyEnvironment([
        'APP_ENV' => 'production',
        'SESSION_SECURE' => 'true',
        'RATE_LIMIT_ENABLED' => 'true',
        'RATE_LIMIT_MODE' => 'enforce',
        'RATE_LIMIT_IDENTITY_SALT' => str_repeat('b', 64),
        $key => $value,
    ]);

    expect(fn () => ProductionSecurityPolicy::assertEnvironment())
        ->toThrow(\RuntimeException::class);
})->with([
    'insecure session' => ['SESSION_SECURE', 'false'],
    'disabled rate limiting' => ['RATE_LIMIT_ENABLED', 'false'],
    'report-only rate limiting' => ['RATE_LIMIT_MODE', 'report_only'],
    'placeholder identity salt' => ['RATE_LIMIT_IDENTITY_SALT', 'change-me'],
]);
