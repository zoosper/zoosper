<?php

declare(strict_types=1);

use Zoosper\Core\Routing\FallbackHandlerInterface;
use Zoosper\Core\Routing\NullFallbackHandler;
use Zoosper\Core\Site\NullSiteContextProvider;
use Zoosper\Core\Site\SiteContextProviderInterface;

it('adds core-owned fallback handler contract with safe null implementation', function (): void {
    $handler = new NullFallbackHandler();

    expect($handler)->toBeInstanceOf(FallbackHandlerInterface::class);
    expect($handler->supports(new stdClass()))->toBeFalse();
    expect($handler->handle(new stdClass()))->toBeNull();
});

it('adds core-owned site context provider contract with safe null implementation', function (): void {
    $provider = new NullSiteContextProvider();

    expect($provider)->toBeInstanceOf(SiteContextProviderInterface::class);
    expect($provider->resolve(new stdClass()))->toBeNull();
});

it('keeps core decoupling contract audit tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-core-decoupling-contracts.php')->toBeFile();
});
