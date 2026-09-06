<?php

declare(strict_types=1);

it('declares four public stateful Admin password-reset routes', function (): void {
    $root = dirname(__DIR__, 5);
    $routes = require $root . '/app/zoosper-admin/config/admin_routes.php';
    $map = [];
    foreach ($routes as $route) {
        $map[$route['method'] . ' ' . $route['path']] = $route;
    }
    foreach (['GET /admin/forgot-password', 'POST /admin/forgot-password', 'GET /admin/reset-password', 'POST /admin/reset-password'] as $key) {
        expect($map)->toHaveKey($key)->and($map[$key]['public'] ?? false)->toBeTrue();
    }
});

it('keeps public responses neutral CSRF protected secret safe and unauthenticated', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/PasswordResetController.php');
    $login = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/LoginController.php');
    expect($controller)->toContain('self::NEUTRAL_MESSAGE')
        ->toContain('$this->rateLimiter?->checkPasswordResetRequest($email, $request->clientIp())')
        ->toContain('if ($decision !== null && !$decision->allowed)')
        ->toContain('return $this->neutralResponse()')
        ->toContain('$this->resets->issueForEmail($email)')
        ->toContain('$this->urls->build($issue->token)')
        ->not->toContain('$this->resets->issue($email)')
        ->not->toContain('$issue->plaintextToken')
        ->toContain('$this->csrf->token()')
        ->toContain('$this->csrf->rotate()')
        ->toContain("autocomplete=\"new-password\"")
        ->toContain("name=\"password_confirmation\"")
        ->toContain("name=\"token\"")
        ->toContain("meta name=\"robots\" content=\"noindex,nofollow\"")
        ->toContain("'admin.password_reset_completed'")
        ->not->toContain('SessionGuard')
        ->not->toContain('->login(')
        ->not->toContain('ipAddress:')
        ->not->toContain('userAgent:')
        ->and($login)->toContain('Forgot password?')
        ->toContain('$this->adminUrl(\'forgot-password\')');
});

it('wires only the Auth-owned delivery abstraction into Admin', function (): void {
    $root = dirname(__DIR__, 5);
    $factory = (string) file_get_contents($root . '/app/zoosper-admin/config/controllers.php');
    expect($factory)->toContain('AdminPasswordResetDeliveryInterface::class')
        ->toContain('AdminPasswordResetService::class')
        ->toContain('AdminPasswordResetUrlBuilder::class')
        ->not->toContain('SmtpAdminPasswordResetDelivery')
        ->not->toContain('Zoosper\\Mail\\');
});
