<?php

declare(strict_types=1);

namespace Zoosper\Auth\Service;

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;

final class AuthService
{
    /**
     * Cached dummy hash used to equalise timing when no valid user is found.
     * Generated once via the real hasher so its verification cost matches a
     * genuine password check (same algorithm/cost), which is what makes the
     * timing constant.
     */
    private ?string $dummyHash = null;

    public function __construct(
        private readonly AdminUserRepository $users,
        private readonly PasswordHasher $hasher,
    ) {
    }

    public function authenticate(string $email, string $password): ?AdminUser
    {
        $user = $this->users->findByEmail($email);

        // Constant-time guard (Phase 1.102): always run a real password
        // verification, even when the user is missing or inactive, so the
        // response time for "unknown email" matches "known email, wrong
        // password". This removes the username-enumeration timing side-channel.
        $hashToCheck = ($user !== null && $user->isActive() && $user->passwordHash !== '')
            ? $user->passwordHash
            : $this->dummyHash();

        $passwordValid = $this->hasher->verify($password, $hashToCheck);

        if ($user === null || !$user->isActive()) {
            return null;
        }

        if (!$passwordValid) {
            return null;
        }

        // Phase E1: transparently upgrade the stored hash if PHP's default
        // algorithm/cost has changed since it was created (e.g. after a PHP
        // upgrade, or a deliberate cost increase). This is only possible here,
        // at the exact moment we have the plaintext password in memory —
        // password_hash() output cannot be converted to a different
        // algorithm/cost after the fact without the original plaintext.
        //
        // Note on timing: this adds a small amount of extra work ONLY on a
        // successful login that also needs a rehash. This is not a new
        // enumeration side-channel — the account-existence/wrong-password
        // decision has already been made in the two branches above, and an
        // attacker would need VALID credentials to ever observe this timing,
        // at which point enumeration is moot. This is the same standard
        // pattern used by Laravel, Symfony and WordPress.
        if ($this->hasher->needsRehash($user->passwordHash)) {
            $this->users->updatePassword($user->id, $this->hasher->hash($password));
        }

        $this->users->updateLastLogin($user->id);

        return $this->users->findById($user->id);
    }

    /**
     * Lazily produce (and cache) a valid dummy hash using the real hasher, so
     * the fake verification path costs the same as a real one.
     */
    private function dummyHash(): string
    {
        if ($this->dummyHash === null) {
            // Hash a random, unusable secret; the value is never compared for
            // equality, only used to spend the same CPU as a real verify.
            $this->dummyHash = $this->hasher->hash(bin2hex(random_bytes(32)));
        }

        return $this->dummyHash;
    }
}
