<?php

declare(strict_types=1);

it('rotates CSRF only after successful password authentication and clears it on logout', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-admin/src/Controller/LoginController.php');
    $authenticate = strpos($source, '$this->auth->authenticate($email, $password)');
    $failed = strpos($source, 'if ($user === null)');
    $rotate = strpos($source, '$this->csrf->rotate();');
    $twoFactor = strpos($source, '$this->requiresTwoFactorChallenge($user)');

    expect($authenticate)->not->toBeFalse()
        ->and($failed)->not->toBeFalse()
        ->and($rotate)->not->toBeFalse()
        ->and($twoFactor)->not->toBeFalse()
        ->and($authenticate)->toBeLessThan($failed)
        ->and($failed)->toBeLessThan($rotate)
        ->and($rotate)->toBeLessThan($twoFactor)
        ->and($source)->toContain('$this->csrf->clear();')
        ->toContain('$this->guard->logout();');
});

it('rotates CSRF after successful two-factor promotion', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-two-factor/src/Controller/AdminTwoFactorChallengeController.php');
    $promote = strpos($source, '$this->guard->completeTwoFactorChallenge($user)');
    $rotate = strpos($source, '$this->csrf->rotate();');

    expect($promote)->not->toBeFalse()
        ->and($rotate)->not->toBeFalse()
        ->and($promote)->toBeLessThan($rotate);
});
