<?php

declare(strict_types=1);

use Zoosper\Core\Security\SecurityHeaders;

/*
 * Phase 1.101 behavioural tests for CSP + HSTS.
 *
 * Uses SecurityHeaders::resolvedHeaders() so we can assert exactly which headers
 * would be sent, under different request schemes and config, without touching
 * real PHP response headers.
 */

beforeEach(function (): void {
    $this->originalServer = $_SERVER;
    unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['SERVER_PORT']);
});

afterEach(function (): void {
    $_SERVER = $this->originalServer;
});

function staticHeaders(): array
{
    return [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
    ];
}

it('always emits the configured static headers', function (): void {
    $headers = (new SecurityHeaders(staticHeaders()))->resolvedHeaders();

    expect($headers)->toHaveKey('X-Content-Type-Options')
        ->and($headers['X-Frame-Options'])->toBe('DENY');
});

it('emits CSP in report-only mode by default', function (): void {
    $csp = [
        'enabled' => true,
        'report_only' => true,
        'policy' => "default-src 'self'",
    ];

    $headers = (new SecurityHeaders(staticHeaders(), $csp))->resolvedHeaders();

    expect($headers)->toHaveKey('Content-Security-Policy-Report-Only')
        ->and($headers)->not->toHaveKey('Content-Security-Policy')
        ->and($headers['Content-Security-Policy-Report-Only'])->toBe("default-src 'self'");
});

it('emits an enforcing CSP header when report_only is false', function (): void {
    $csp = [
        'enabled' => true,
        'report_only' => false,
        'policy' => "default-src 'self'",
    ];

    $headers = (new SecurityHeaders(staticHeaders(), $csp))->resolvedHeaders();

    expect($headers)->toHaveKey('Content-Security-Policy')
        ->and($headers)->not->toHaveKey('Content-Security-Policy-Report-Only');
});

it('appends report_uri to CSP header when configured', function (): void {
    $csp = [
        'enabled' => true,
        'report_only' => true,
        'policy' => "default-src 'self'",
        'report_uri' => '/api/csp-report',
    ];

    $headers = (new SecurityHeaders(staticHeaders(), $csp))->resolvedHeaders();

    expect($headers['Content-Security-Policy-Report-Only'])->toBe("default-src 'self'; report-uri /api/csp-report");
});

it('omits CSP when disabled or empty', function (): void {
    $disabled = (new SecurityHeaders(staticHeaders(), ['enabled' => false, 'policy' => "default-src 'self'"]))->resolvedHeaders();
    $empty = (new SecurityHeaders(staticHeaders(), ['enabled' => true, 'policy' => '']))->resolvedHeaders();

    expect($disabled)->not->toHaveKey('Content-Security-Policy-Report-Only')
        ->and($disabled)->not->toHaveKey('Content-Security-Policy')
        ->and($empty)->not->toHaveKey('Content-Security-Policy-Report-Only');
});

it('emits HSTS only on HTTPS requests', function (): void {
    $hsts = [
        'enabled' => true,
        'max_age' => 31536000,
        'include_subdomains' => true,
        'preload' => false,
    ];

    // Plain HTTP -> no HSTS.
    $_SERVER['SERVER_PORT'] = '80';
    $http = (new SecurityHeaders(staticHeaders(), [], $hsts))->resolvedHeaders();
    expect($http)->not->toHaveKey('Strict-Transport-Security');

    // HTTPS -> HSTS present with includeSubDomains, no preload.
    $_SERVER['HTTPS'] = 'on';
    $https = (new SecurityHeaders(staticHeaders(), [], $hsts))->resolvedHeaders();
    expect($https)->toHaveKey('Strict-Transport-Security')
        ->and($https['Strict-Transport-Security'])->toBe('max-age=31536000; includeSubDomains');
});

it('includes preload in HSTS when enabled over HTTPS', function (): void {
    $_SERVER['HTTPS'] = 'on';
    $hsts = [
        'enabled' => true,
        'max_age' => 63072000,
        'include_subdomains' => true,
        'preload' => true,
    ];

    $headers = (new SecurityHeaders(staticHeaders(), [], $hsts))->resolvedHeaders();

    expect($headers['Strict-Transport-Security'])->toBe('max-age=63072000; includeSubDomains; preload');
});

it('omits HSTS entirely when disabled even on HTTPS', function (): void {
    $_SERVER['HTTPS'] = 'on';

    $headers = (new SecurityHeaders(staticHeaders(), [], ['enabled' => false]))->resolvedHeaders();

    expect($headers)->not->toHaveKey('Strict-Transport-Security');
});










