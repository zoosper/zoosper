<?php

declare(strict_types=1);
it('keeps API password login behind rate limiting and the second-factor boundary', function (): void {
    $root = dirname(__DIR__, 3);
    $controller = (string) file_get_contents($root . '/src/Controller/AuthController.php');
    $wiring = (string) file_get_contents($root . '/config/controllers.php');
    expect($controller)
        ->toContain('checkPasswordLogin($email, $request->clientIp())')
        ->toContain("'too_many_attempts'")
        ->toContain("'Retry-After'")
        ->toContain('requiresSecondFactor($user->id)')
        ->toContain("'second_factor_required'")
        ->toContain('$this->guard->logout();')
        ->not->toContain('$this->guard->login($user);\n        return $this->json->success')
        ->and($wiring)->toContain('SecondFactorRequirementInterface::class')
        ->toContain('AdminAuthenticationRateLimiterInterface::class');
});
it('uses an Auth-owned fail-closed contract with a Two Factor implementation', function (): void {
    $root = dirname(__DIR__, 3);
    $default = (string) file_get_contents(dirname($root) . '/zoosper-auth/src/Service/RequireSecondFactorByDefault.php');
    $provider = (string) file_get_contents(dirname($root) . '/zoosper-two-factor/src/Service/AdminSecondFactorRequirement.php');
    expect($default)->toContain('return true;')
        ->and($provider)->toContain('return !$this->enrollment->requiresEnrollment($adminUserId);');
});
