<?php

declare(strict_types=1);

use Zoosper\Auth\Service\PasswordHasher;

/*
 * Phase E1 behavioural tests for PasswordHasher::needsRehash().
 */

it('reports a freshly hashed password does NOT need rehashing', function (): void {
    $hasher = new PasswordHasher();
    $hash = $hasher->hash('CorrectHorseBattery1');

    expect($hasher->needsRehash($hash))->toBeFalse();
});

it('reports a hash with a weaker cost than PASSWORD_DEFAULT DOES need rehashing', function (): void {
    $hasher = new PasswordHasher();
    // Deliberately weak cost (4) — guaranteed to differ from PASSWORD_DEFAULT's
    // cost on any supported PHP version, so this is a portable, deterministic
    // way to prove needsRehash() detects an outdated hash.
    $weakHash = password_hash('CorrectHorseBattery1', PASSWORD_BCRYPT, ['cost' => 4]);

    expect($hasher->needsRehash($weakHash))->toBeTrue();
});

it('hash() output can always be verified by verify()', function (): void {
    $hasher = new PasswordHasher();
    $hash = $hasher->hash('CorrectHorseBattery1');

    expect($hasher->verify('CorrectHorseBattery1', $hash))->toBeTrue()
        ->and($hasher->verify('WrongPassword', $hash))->toBeFalse();
});










