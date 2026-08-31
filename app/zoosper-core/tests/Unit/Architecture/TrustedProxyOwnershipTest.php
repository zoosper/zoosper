<?php

declare(strict_types=1);

it('centralises forwarded proxy interpretation in TrustedProxyResolver', function (): void {
    $root = dirname(__DIR__, 5);
    $application = (string) file_get_contents($root . '/app/zoosper-core/src/Http/Application.php');
    $request = (string) file_get_contents($root . '/app/zoosper-core/src/Http/Request.php');
    $resolver = (string) file_get_contents($root . '/app/zoosper-core/src/Http/TrustedProxyResolver.php');

    expect($application)->toContain('TrustedProxyResolver::fromEnvironment()->isHttps($_SERVER)')
        ->not->toContain("\$_SERVER['HTTP_X_FORWARDED_PROTO']")
        ->and($request)->toContain('TrustedProxyResolver::fromEnvironment()->clientIp($_SERVER)')
        ->and($resolver)->toContain("getenv('TRUSTED_PROXIES')")
        ->toContain("\$server['HTTP_X_FORWARDED_FOR']")
        ->toContain("\$server['HTTP_X_FORWARDED_PROTO']");
});










