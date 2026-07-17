<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Routing;

use Zoosper\Core\Routing\ModuleRouteDefinition;

/**
 * Phase 1.33d-d guard: admin middleware owns authentication and CSRF checks for
 * the 2FA setup routes. The controller still generates form CSRF tokens and
 * still performs OTP/TOTP enrolment validation, but it must not duplicate the
 * middleware-level authentication redirect or POST CSRF decision.
 */

/** @return array<string, array{permissions: list<string>, public: bool}> */
function twoFactorSetupRouteMetadata(): array
{
    $root = dirname(__DIR__, 5);
    $file = $root . '/app/zoosper-two-factor/config/admin_routes.php';
    $config = require $file;
    $map = [];

    foreach (is_array($config) ? $config : [] as $route) {
        if (!is_array($route)) {
            continue;
        }

        $map[strtoupper((string) ($route['method'] ?? 'GET')) . ' ' . (string) ($route['path'] ?? '')] = [
            'permissions' => ModuleRouteDefinition::normalisePermissions($route['permission'] ?? null),
            'public' => (bool) ($route['public'] ?? false),
        ];
    }

    return $map;
}

test('two-factor setup routes remain authenticated admin routes', function () {
    $map = twoFactorSetupRouteMetadata();

    foreach (['GET /admin/2fa/setup', 'POST /admin/2fa/setup'] as $route) {
        expect($map)->toHaveKey($route);
        expect($map[$route]['public'])->toBeFalse();
        expect($map[$route]['permissions'])->toBe([]);
    }
});

test('two-factor setup controller no longer duplicates middleware auth or csrf gates', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-two-factor/src/Controller/AdminTwoFactorSetupController.php');

    expect($source)->not->toContain('return Response::redirect($this->adminUrl(\'/login\'))');
    expect($source)->not->toContain('csrf->isValid');
    expect($source)->toContain('currentAdminUser()');
});

test('two-factor setup still generates csrf tokens and validates authenticator codes', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-two-factor/src/Controller/AdminTwoFactorSetupController.php');

    expect($source)->toContain('$this->csrf->token()');
    expect($source)->toContain('confirm($user->id, $secret');
    expect($source)->toContain('pending_2fa_secret');
});
