<?php

declare(strict_types=1);

use Zoosper\Core\Http\Application;

/*
 * Phase 1.100 behavioural test for the request-aware secure-cookie default.
 *
 * Application::requestIsHttps() decides the DEFAULT value of the session cookie
 * secure flag when SESSION_SECURE is not explicitly set. We assert it detects
 * HTTPS via the standard server var, the X-Forwarded-Proto proxy header, and the
 * canonical HTTPS port, and returns false for plain local HTTP.
 */

beforeEach(function (): void {
    // Snapshot and clear the relevant server vars for a clean slate.
    $this->originalServer = $_SERVER;
    $this->originalTrustedProxies = getenv('TRUSTED_PROXIES');
    putenv('TRUSTED_PROXIES');
    unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['SERVER_PORT']);
});

afterEach(function (): void {
    $_SERVER = $this->originalServer;
    $this->originalTrustedProxies === false
        ? putenv('TRUSTED_PROXIES')
        : putenv('TRUSTED_PROXIES=' . $this->originalTrustedProxies);
});

it('treats plain local HTTP as not https (default secure=false)', function (): void {
    $_SERVER['SERVER_PORT'] = '80';

    expect(Application::requestIsHttps())->toBeFalse();
});

it('detects https via the HTTPS server var', function (): void {
    $_SERVER['HTTPS'] = 'on';

    expect(Application::requestIsHttps())->toBeTrue();
});

it('treats HTTPS=off as not https', function (): void {
    $_SERVER['HTTPS'] = 'off';

    expect(Application::requestIsHttps())->toBeFalse();
});

it('detects https behind a configured trusted reverse proxy via X-Forwarded-Proto', function (): void {
    putenv('TRUSTED_PROXIES=10.0.0.10');
    $_SERVER['REMOTE_ADDR'] = '10.0.0.10';
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

    expect(Application::requestIsHttps())->toBeTrue();
});

it('ignores forwarded https from an untrusted peer', function (): void {
    putenv('TRUSTED_PROXIES=10.0.0.10');
    $_SERVER['REMOTE_ADDR'] = '203.0.113.20';
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

    expect(Application::requestIsHttps())->toBeFalse();
});

it('detects https via the canonical 443 port', function (): void {
    $_SERVER['SERVER_PORT'] = '443';

    expect(Application::requestIsHttps())->toBeTrue();
});
