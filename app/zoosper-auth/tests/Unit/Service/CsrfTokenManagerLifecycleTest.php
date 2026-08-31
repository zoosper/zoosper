<?php

declare(strict_types=1);

use Zoosper\Auth\Service\CsrfTokenManager;

beforeEach(function (): void { $_SESSION = []; });
afterEach(function (): void { $_SESSION = []; });

it('creates validates rotates and clears one session token', function (): void {
    $csrf = new CsrfTokenManager();
    $first = $csrf->token();

    expect($first)->toHaveLength(64)
        ->and($csrf->token())->toBe($first)
        ->and($csrf->isValid($first))->toBeTrue();

    $second = $csrf->rotate();
    expect($second)->toHaveLength(64)
        ->not->toBe($first)
        ->and($csrf->isValid($first))->toBeFalse()
        ->and($csrf->isValid($second))->toBeTrue();

    $csrf->clear();
    expect($_SESSION)->not->toHaveKey('_csrf_token');
});

it('does not accept null or an unrelated token', function (): void {
    $csrf = new CsrfTokenManager();
    $csrf->token();

    expect($csrf->isValid(null))->toBeFalse()
        ->and($csrf->isValid(str_repeat('0', 64)))->toBeFalse();
});










