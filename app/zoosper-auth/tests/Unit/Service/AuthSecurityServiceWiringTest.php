<?php

declare(strict_types=1);

use Zoosper\Auth\Contract\SecondFactorRequirementInterface;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiter;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiterInterface;
use Zoosper\Auth\Service\RequireSecondFactorByDefault;
use Zoosper\Core\Container\ServiceContainer;

it('registers the fail-closed second-factor default', function (): void {
    $factories = require dirname(__DIR__, 3) . '/config/services.php';
    $requirement = $factories[SecondFactorRequirementInterface::class](new ServiceContainer());

    expect($requirement)->toBeInstanceOf(RequireSecondFactorByDefault::class)
        ->and($requirement->requiresSecondFactor(1))->toBeTrue()
        ->and($requirement->requiresSecondFactor(0))->toBeTrue();
});

it('constructs the authentication rate limiter from the service manifest', function (): void {
    $factories = require dirname(__DIR__, 3) . '/config/services.php';
    $services = new ServiceContainer();
    $services->set(PDO::class, new PDO('sqlite::memory:'));

    $limiter = $factories[AdminAuthenticationRateLimiterInterface::class]($services);

    expect($limiter)->toBeInstanceOf(AdminAuthenticationRateLimiter::class);
});