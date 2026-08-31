<?php

declare(strict_types=1);

use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Security\PasswordPolicy;

it('enforces a central configurable minimum for newly supplied passwords', function (): void {
    $policy = new PasswordPolicy(minLength: 12, minCharacterClasses: 2);
    expect($policy->violations('short'))->toContain('Password must be at least 12 characters long.');
    expect($policy->isValid('LongEnough12!'))->toBeTrue();
});

it('detects legacy password hashes that need an automatic upgrade', function (): void {
    $hasher = new PasswordHasher();
    $legacy = password_hash('ChangeMe123!', PASSWORD_BCRYPT, ['cost' => 4]);
    expect($hasher->verify('ChangeMe123!', $legacy))->toBeTrue()
        ->and($hasher->needsRehash($legacy))->toBeTrue()
        ->and($hasher->needsRehash($hasher->hash('ChangeMe123!')))->toBeFalse();
});










