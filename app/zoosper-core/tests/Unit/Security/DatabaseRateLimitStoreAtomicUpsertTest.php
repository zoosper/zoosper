<?php

declare(strict_types=1);

use Zoosper\Core\Security\RateLimit\DatabaseRateLimitStore;
use Zoosper\Core\Security\RateLimit\RateLimitRule;

function rateLimitAtomicTestPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

it('does not throw when a row already exists for the exact same identity+window', function (): void {
    $pdo = rateLimitAtomicTestPdo();
    $store = new DatabaseRateLimitStore($pdo);
    $store->ensureSchema();

    $rule = new RateLimitRule('admin.login', maxAttempts: 5, windowSeconds: 300, scope: 'admin');
    $identityHash = str_repeat('a', 64);
    $now = 1_700_000_000;
    $windowStartsAt = intdiv($now, $rule->windowSeconds) * $rule->windowSeconds;

    $pdo->prepare(
        'INSERT INTO rate_limit_buckets (scope, identity_hash, rule_key, window_starts_at, window_ends_at, attempts, created_at, updated_at) '
        . 'VALUES (:scope, :identity_hash, :rule_key, :window_starts_at, :window_ends_at, 1, :now, :now)'
    )->execute([
        ':scope' => $rule->scope,
        ':identity_hash' => $identityHash,
        ':rule_key' => $rule->key,
        ':window_starts_at' => $windowStartsAt,
        ':window_ends_at' => $windowStartsAt + $rule->windowSeconds,
        ':now' => $now,
    ]);

    $decision = $store->recordAttempt($rule, $identityHash, $now);

    expect($decision->allowed)->toBeTrue();
    expect($decision->attempts)->toBe(2);
});

it('creates a new bucket correctly on the first attempt', function (): void {
    $pdo = rateLimitAtomicTestPdo();
    $store = new DatabaseRateLimitStore($pdo);
    $store->ensureSchema();

    $rule = new RateLimitRule('admin.login', maxAttempts: 5, windowSeconds: 300, scope: 'admin');
    $decision = $store->recordAttempt($rule, str_repeat('b', 64), 1_700_000_000);

    expect($decision->allowed)->toBeTrue();
    expect($decision->attempts)->toBe(1);
});

it('correctly denies once max attempts is exceeded', function (): void {
    $pdo = rateLimitAtomicTestPdo();
    $store = new DatabaseRateLimitStore($pdo);
    $store->ensureSchema();

    $rule = new RateLimitRule('admin.login', maxAttempts: 3, windowSeconds: 300, scope: 'admin');
    $identityHash = str_repeat('c', 64);
    $now = 1_700_000_000;

    $decisions = [];
    for ($i = 0; $i < 5; $i++) {
        $decisions[] = $store->recordAttempt($rule, $identityHash, $now);
    }

    expect($decisions[2]->allowed)->toBeTrue();
    expect($decisions[3]->allowed)->toBeFalse();
    expect($decisions[4]->attempts)->toBe(5);
});

it('keeps separate identities in independent buckets', function (): void {
    $pdo = rateLimitAtomicTestPdo();
    $store = new DatabaseRateLimitStore($pdo);
    $store->ensureSchema();

    $rule = new RateLimitRule('admin.login', maxAttempts: 2, windowSeconds: 300, scope: 'admin');
    $now = 1_700_000_000;

    $decisionA = $store->recordAttempt($rule, str_repeat('d', 64), $now);
    $decisionB = $store->recordAttempt($rule, str_repeat('e', 64), $now);

    expect($decisionA->attempts)->toBe(1);
    expect($decisionB->attempts)->toBe(1);
});

it('resets a bucket correctly', function (): void {
    $pdo = rateLimitAtomicTestPdo();
    $store = new DatabaseRateLimitStore($pdo);
    $store->ensureSchema();

    $rule = new RateLimitRule('admin.login', maxAttempts: 2, windowSeconds: 300, scope: 'admin');
    $identityHash = str_repeat('f', 64);
    $now = 1_700_000_000;

    $store->recordAttempt($rule, $identityHash, $now);
    $store->recordAttempt($rule, $identityHash, $now);
    $store->reset($rule, $identityHash);

    $decisionAfterReset = $store->recordAttempt($rule, $identityHash, $now);
    expect($decisionAfterReset->attempts)->toBe(1);
});










