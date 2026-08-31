<?php

declare(strict_types=1);

use Zoosper\Auth\Security\PasswordPolicy;

/*
 * Phase 1.110 behavioural tests for the admin password policy (Sonnet §5).
 * Pure unit tests — no database, no hashing dependency.
 */

it('rejects passwords shorter than the minimum length', function (): void {
    $policy = new PasswordPolicy(minLength: 12, minCharacterClasses: 2);

    $violations = $policy->violations('Ab1!');

    expect($policy->isValid('Ab1!'))->toBeFalse()
        ->and($violations)->not->toBe([])
        ->and(implode(' ', $violations))->toContain('at least 12 characters');
});

it('rejects a long password with only one character class', function (): void {
    $policy = new PasswordPolicy(minLength: 12, minCharacterClasses: 2);

    // 16 lowercase letters only — long enough, but only one character class.
    expect($policy->isValid('aaaaaaaaaaaaaaaa'))->toBeFalse();
});

it('accepts a password meeting length and character-class requirements', function (): void {
    $policy = new PasswordPolicy(minLength: 12, minCharacterClasses: 2);

    expect($policy->isValid('CorrectHorse1'))->toBeTrue()
        ->and($policy->violations('CorrectHorse1'))->toBe([]);
});

it('is configurable via constructor thresholds', function (): void {
    $lenient = new PasswordPolicy(minLength: 6, minCharacterClasses: 1);

    expect($lenient->isValid('abcdef'))->toBeTrue();

    $strict = new PasswordPolicy(minLength: 16, minCharacterClasses: 4);

    expect($strict->isValid('CorrectHorse1'))->toBeFalse() // too short for strict, and missing symbol
        ->and($strict->isValid('Correct-Horse-Battery1'))->toBeTrue();
});

it('counts unicode length correctly rather than byte length', function (): void {
    $policy = new PasswordPolicy(minLength: 12, minCharacterClasses: 1);

    // 12 multibyte characters — mb_strlen must count characters, not bytes.
    $password = str_repeat('é', 12);
    expect(mb_strlen($password))->toBe(12)
        ->and($policy->violations($password))->toBe([]);
});

it('rejects an empty password', function (): void {
    $policy = new PasswordPolicy();

    expect($policy->isValid(''))->toBeFalse();
});










