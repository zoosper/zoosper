<?php

declare(strict_types=1);

namespace Zoosper\Auth\Service;

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;

/**
 * Session-backed authentication guard.
 *
 * Two responsibilities were added in Phase 1.105:
 *
 *  1. Per-request memoization of the resolved AdminUser (Sonnet Phase 2 §3.1).
 *     `user()` previously re-queried the database (user row + permissions join)
 *     on every call — 3–5× per admin page render. It now resolves once per
 *     request and caches the result, invalidating on login()/logout().
 *
 *  2. A pending-2FA session state (foundation for login-time 2FA enforcement,
 *     Sonnet Phase 2 §1). After a correct password for a 2FA-enrolled user, the
 *     login controller calls beginTwoFactorChallenge() instead of login(). While
 *     a session is only "pending 2FA", user() still returns null, so
 *     AuthenticationMiddleware keeps the session UNAUTHORISED until a valid OTP /
 *     recovery code promotes it via completeTwoFactorChallenge().
 *
 * The class is no longer `readonly` because it must cache the resolved user for
 * the request; the injected repository dependency remains readonly.
 */
final class SessionGuard
{
    private const SESSION_USER_KEY = 'admin_user_id';
    private const SESSION_PENDING_2FA_KEY = 'pending_2fa_user_id';
    private const SESSION_LAST_ACTIVITY_KEY = 'admin_last_activity_at';

    /** Per-request cache of the resolved user (false = not yet resolved). */
    private AdminUser|false|null $cachedUser = false;

    public function __construct(
        private readonly AdminUserRepository $users,
        private readonly int $idleTimeoutSeconds = 7200,
        private readonly ?\Closure $clock = null,
    ) {
    }

    /**
     * Fully authenticate a user (password + any required 2FA already satisfied).
     */
    public function login(AdminUser $user): void
    {
        session_regenerate_id(true);
        unset($_SESSION[self::SESSION_PENDING_2FA_KEY]);
        $_SESSION[self::SESSION_USER_KEY] = $user->id;
        $this->touch();

        // Prime the per-request cache; no need to re-query immediately.
        $this->cachedUser = $user;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $this->cachedUser = null;
    }

    /**
     * The fully-authenticated admin user, or null. A session that is only
     * pending 2FA is NOT authenticated and returns null here by design.
     */
    public function user(): ?AdminUser
    {
        if ($this->expireIfIdle()) {
            return null;
        }

        if ($this->cachedUser !== false) {
            $this->touch();
            return $this->cachedUser;
        }

        $id = $_SESSION[self::SESSION_USER_KEY] ?? null;
        $this->cachedUser = is_numeric($id) ? $this->users->findById((int) $id) : null;
        if ($this->cachedUser !== null) {
            $this->touch();
        }

        return $this->cachedUser;
    }

    public function requirePermission(string $permission): ?AdminUser
    {
        $user = $this->user();

        if ($user === null || !$user->can($permission)) {
            return null;
        }

        return $user;
    }

    // ---------------------------------------------------------------- 2FA seam

    /**
     * Begin a pending-2FA session after a correct password. The session records
     * WHO is authenticating but is NOT authorised until the challenge completes.
     */
    public function beginTwoFactorChallenge(AdminUser $user): void
    {
        session_regenerate_id(true);
        unset($_SESSION[self::SESSION_USER_KEY]);
        $_SESSION[self::SESSION_PENDING_2FA_KEY] = $user->id;
        $this->touch();
        $this->cachedUser = null;
    }

    /**
     * The admin-user id awaiting a 2FA challenge, or null when none is pending.
     */
    public function pendingTwoFactorUserId(): ?int
    {
        if ($this->expireIfIdle()) {
            return null;
        }

        $id = $_SESSION[self::SESSION_PENDING_2FA_KEY] ?? null;
        if (is_numeric($id)) {
            $this->touch();
        }

        return is_numeric($id) ? (int) $id : null;
    }

    public function hasPendingTwoFactorChallenge(): bool
    {
        return $this->pendingTwoFactorUserId() !== null;
    }

    /**
     * Promote a pending-2FA session to fully authenticated once the OTP /
     * recovery code has been verified. The supplied user's id MUST match the
     * pending id, otherwise the promotion is refused (defence against fixation).
     */
    public function completeTwoFactorChallenge(AdminUser $user): bool
    {
        if ($this->pendingTwoFactorUserId() !== $user->id) {
            return false;
        }

        $this->login($user);

        return true;
    }

    public function clearPendingTwoFactorChallenge(): void
    {
        unset($_SESSION[self::SESSION_PENDING_2FA_KEY]);
        if (!isset($_SESSION[self::SESSION_USER_KEY])) {
            unset($_SESSION[self::SESSION_LAST_ACTIVITY_KEY]);
        }
    }

    private function expireIfIdle(): bool
    {
        if ($this->idleTimeoutSeconds === 0) {
            return false;
        }

        $hasProtectedState = isset(
            $_SESSION[self::SESSION_USER_KEY],
        ) || isset($_SESSION[self::SESSION_PENDING_2FA_KEY]);
        if (!$hasProtectedState) {
            unset($_SESSION[self::SESSION_LAST_ACTIVITY_KEY]);
            return false;
        }

        $lastActivity = $_SESSION[self::SESSION_LAST_ACTIVITY_KEY] ?? null;
        $now = $this->now();
        if (!is_numeric($lastActivity)) {
            $this->clearAuthenticationState();
            return true;
        }

        $lastActivity = (int) $lastActivity;
        if ($lastActivity <= $now && $now - $lastActivity <= $this->idleTimeoutSeconds) {
            return false;
        }

        $this->clearAuthenticationState();
        return true;
    }

    private function clearAuthenticationState(): void
    {
        unset(
            $_SESSION[self::SESSION_USER_KEY],
            $_SESSION[self::SESSION_PENDING_2FA_KEY],
            $_SESSION[self::SESSION_LAST_ACTIVITY_KEY],
        );
        $this->cachedUser = null;
    }

    private function touch(): void
    {
        if ($this->idleTimeoutSeconds > 0) {
            $_SESSION[self::SESSION_LAST_ACTIVITY_KEY] = $this->now();
        }
    }

    private function now(): int
    {
        return $this->clock !== null ? (int) ($this->clock)() : time();
    }
}
