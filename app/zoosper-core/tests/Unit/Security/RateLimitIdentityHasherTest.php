<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\DatabaseRateLimitStore;
use Zoosper\Core\Security\RateLimit\RateLimitIdentityHasher;
use Zoosper\Core\Security\RateLimit\RateLimitRule;

it('separates delimiter collisions in identities and salts', function (): void {
    $hasher = new RateLimitIdentityHasher();

    expect($hasher->hash(['alice|127.0.0.1'], 'salt'))
        ->not->toBe($hasher->hash(['alice', '127.0.0.1'], 'salt'))
        ->and($hasher->hash(['b|c'], 'a'))
        ->not->toBe($hasher->hash(['c'], 'a|b'))
        ->and($hasher->hash(['a|b', 'c'], 'salt'))
        ->not->toBe($hasher->hash(['a', 'b|c'], 'salt'));
});

it('does not let a colliding identity consume or reset another bucket', function (): void {
    $hasher = new RateLimitIdentityHasher();
    $store = new DatabaseRateLimitStore(new PDO('sqlite::memory:'));
    $store->ensureSchema();
    $rule = new RateLimitRule('test', 1, 60, 'admin');
    $first = $hasher->hash(['alice|127.0.0.1'], 'salt');
    $second = $hasher->hash(['alice', '127.0.0.1'], 'salt');

    expect($store->recordAttempt($rule, $first, 120)->allowed)->toBeTrue()
        ->and($store->recordAttempt($rule, $second, 120)->allowed)->toBeTrue();
    $store->reset($rule, $first);
    expect($store->recordAttempt($rule, $second, 120)->allowed)->toBeFalse();
});

it('preserves legacy hashes and normalisation for unambiguous inputs', function (): void {
    $hasher = new RateLimitIdentityHasher();

    expect($hasher->hash([' alice ', '', '127.0.0.1'], 'salt'))
        ->toBe(hash('sha256', 'salt|alice|127.0.0.1'))
        ->and($hasher->hash(['alice'], ''))->toBe(hash('sha256', '|alice'));
});

it('separates binary inputs and rejects an empty identity', function (): void {
    $hasher = new RateLimitIdentityHasher();
    expect($hasher->hash(["a\0b|c"], 'salt'))
        ->not->toBe($hasher->hash(["a\0b", 'c'], 'salt'));
    $hasher->hash([' ', '']);
})->throws(InvalidArgumentException::class);