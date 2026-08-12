<?php

declare(strict_types=1);

it('uses one canonical configured PasswordPolicy across HTTP and admin:create', function (): void {
    $root = dirname(__DIR__, 3);
    $services = (string) file_get_contents($root . '/config/services.php');
    $controllers = (string) file_get_contents($root . '/config/controllers.php');
    $userController = (string) file_get_contents($root . '/src/Admin/Controller/UserAdminController.php');
    $command = (string) file_get_contents($root . '/src/Console/AdminCreateCommand.php');

    expect($root . '/src/Service/PasswordPolicy.php')->not->toBeFile()
        ->and($root . '/src/Security/PasswordPolicy.php')->toBeFile()
        ->and($services)->toContain('use Zoosper\\Auth\\Security\\PasswordPolicy;')
        ->not->toContain('use Zoosper\\Auth\\Service\\PasswordPolicy;')
        ->toContain('minCharacterClasses:')
        ->and($controllers)->toContain('passwordPolicy: $services->get(PasswordPolicy::class)')
        ->and($userController)->toContain('$policy->violations($password)')
        ->and($command)->toContain('private ?PasswordPolicy $passwordPolicy = null')
        ->toContain('$violations = $policy->violations($password)');
});

it('keeps automatic password rehash in the established authentication runtime', function (): void {
    $root = dirname(__DIR__, 3);
    $auth = (string) file_get_contents($root . '/src/Service/AuthService.php');
    $hasher = (string) file_get_contents($root . '/src/Service/PasswordHasher.php');

    expect($auth)->toContain('$this->hasher->needsRehash($user->passwordHash)')
        ->toContain('$this->users->updatePassword(')
        ->and($hasher)->toContain('password_needs_rehash($hash, PASSWORD_DEFAULT)');
});
