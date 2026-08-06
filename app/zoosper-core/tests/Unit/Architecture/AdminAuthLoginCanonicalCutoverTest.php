<?php

declare(strict_types=1);

it('wires authentication csrf and rate limiting to the canonical admin URLs', function (): void {
    $root = dirname(__DIR__, 5);
    $services = (string) file_get_contents($root . '/app/zoosper-auth/config/services.php');
    $csrf = (string) file_get_contents($root . '/app/zoosper-auth/src/Http/CsrfMiddleware.php');
    $rateLimit = (string) file_get_contents($root . '/app/zoosper-auth/src/Http/RateLimitReportOnlyAdminMiddleware.php');

    expect($services)->toContain("->url('login')")
        ->toContain('->basePath()')
        ->and($csrf)->toContain("private string \$adminHomePath = '/admin'")
        ->toContain('htmlspecialchars($this->adminHomePath')
        ->and($rateLimit)->toContain("private readonly string \$loginPath = '/admin/login'")
        ->toContain('$request->path() !== $this->loginPath')
        ->not->toContain('private const LOGIN_PATH');
});

it('routes every login controller destination through the canonical generator', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/LoginController.php');
    $wiring = (string) file_get_contents($root . '/app/zoosper-admin/config/controllers.php');

    expect($controller)->toContain('private ?AdminUrlGenerator $adminUrls = null')
        ->toContain("\$this->adminUrl('2fa/challenge')")
        ->toContain("\$this->adminUrl('login')")
        ->toContain('$this->adminUrl()')
        ->toContain('$action = $this->e($this->adminUrl(\'login\'))')
        ->not->toContain('action="/admin/login"')
        ->and($wiring)->toContain('$services->get(AdminUrlGenerator::class)');
});
