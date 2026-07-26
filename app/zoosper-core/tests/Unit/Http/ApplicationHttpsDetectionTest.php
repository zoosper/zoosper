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
    unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO'], $_SERVER['SERVER_PORT']);
});

afterEach(function (): void {
    $_SERVER = $this->originalServer;
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

it('detects https behind a reverse proxy via X-Forwarded-Proto', function (): void {
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';

    expect(Application::requestIsHttps())->toBeTrue();
});

it('detects https via the canonical 443 port', function (): void {
    $_SERVER['SERVER_PORT'] = '443';

    expect(Application::requestIsHttps())->toBeTrue();
});
