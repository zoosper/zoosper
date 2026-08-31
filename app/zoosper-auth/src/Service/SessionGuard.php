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
    private const SESSION_PASSWORD_HASH_KEY = 'admin_password_hash_fingerprint';
    private const SESSION_LAST_ACTIVITY_KEY = 'admin_last_activity_at';
    private const SESSION_CREATED_AT_KEY = 'admin_session_created_at';

    /** Per-request cache of the resolved user (false = not yet resolved). */
    private AdminUser|false|null $cachedUser = false;

    private readonly int $absoluteLifetimeSeconds;
    private readonly ?\Closure $clock;

    public function __construct(
        private readonly AdminUserRepository $users,
        private readonly int $idleTimeoutSeconds = 7200,
        int|\Closure|null $absoluteLifetimeSecondsOrClock = 86400,
        ?\Closure $clock = null,
    ) {
        if ($absoluteLifetimeSecondsOrClock instanceof \Closure) {
            $this->absoluteLifetimeSeconds = 86400;
            $this->clock = $absoluteLifetimeSecondsOrClock;
        } else {
            $this->absoluteLifetimeSeconds = $absoluteLifetimeSecondsOrClock ?? 86400;
            $this->clock = $clock;
        }
    }

    /**
     * Fully authenticate a user (password + any required 2FA already satisfied).
     */
    public function login(AdminUser $user): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        unset($_SESSION[self::SESSION_PENDING_2FA_KEY]);
        $_SESSION[self::SESSION_USER_KEY] = $user->id;
        $_SESSION[self::SESSION_PASSWORD_HASH_KEY] = hash('sha256', $user->passwordHash);
        if (!isset($_SESSION[self::SESSION_CREATED_AT_KEY])) {
            $_SESSION[self::SESSION_CREATED_AT_KEY] = $this->now();
        }
        $this->touch();

        // Prime the per-request cache; no need to re-query immediately.
        $this->cachedUser = $user;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]);
            }
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
        $resolved = is_numeric($id) ? $this->users->findById((int) $id) : null;
        if ($resolved !== null) {
            $storedFingerprint = $_SESSION[self::SESSION_PASSWORD_HASH_KEY] ?? null;
            $currentFingerprint = hash('sha256', $resolved->passwordHash);
            if ($storedFingerprint !== null && !hash_equals($storedFingerprint, $currentFingerprint)) {
                $this->logout();
                return null;
            }
            if ($storedFingerprint === null && $resolved->passwordHash !== '') {
                $_SESSION[self::SESSION_PASSWORD_HASH_KEY] = $currentFingerprint;
            }
            $this->touch();
        }
        $this->cachedUser = $resolved;

        return $this->cachedUser;
    }

    public function refreshPasswordHashFingerprint(string $passwordHash): void
    {
        $_SESSION[self::SESSION_PASSWORD_HASH_KEY] = hash('sha256', $passwordHash);
    }

    public function clearUserCache(): void
    {
        $this->cachedUser = false;
    }

    public function reset(): void
    {
        $this->clearUserCache();
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
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        unset($_SESSION[self::SESSION_USER_KEY]);
        $_SESSION[self::SESSION_PENDING_2FA_KEY] = $user->id;
        if (!isset($_SESSION[self::SESSION_CREATED_AT_KEY])) {
            $_SESSION[self::SESSION_CREATED_AT_KEY] = $this->now();
        }
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
            unset($_SESSION[self::SESSION_LAST_ACTIVITY_KEY], $_SESSION[self::SESSION_CREATED_AT_KEY]);
        }
    }

    private function expireIfIdle(): bool
    {
        $hasProtectedState = isset(
            $_SESSION[self::SESSION_USER_KEY],
        ) || isset($_SESSION[self::SESSION_PENDING_2FA_KEY]);
        if (!$hasProtectedState) {
            unset($_SESSION[self::SESSION_LAST_ACTIVITY_KEY], $_SESSION[self::SESSION_CREATED_AT_KEY]);
            return false;
        }

        $now = $this->now();

        if ($this->absoluteLifetimeSeconds > 0) {
            $createdAt = $_SESSION[self::SESSION_CREATED_AT_KEY] ?? null;
            if ($createdAt === null) {
                $_SESSION[self::SESSION_CREATED_AT_KEY] = $now;
                $createdAt = $now;
            }
            if (!is_numeric($createdAt) || ($now - (int) $createdAt) > $this->absoluteLifetimeSeconds) {
                $this->clearAuthenticationState();
                return true;
            }
        }

        if ($this->idleTimeoutSeconds === 0) {
            return false;
        }

        $lastActivity = $_SESSION[self::SESSION_LAST_ACTIVITY_KEY] ?? null;
        if (!is_numeric($lastActivity)) {
            $this->clearAuthenticationState();
            return true;
        }

        $lastActivity = (int) $lastActivity;
        if ($lastActivity <= $now && ($now - $lastActivity) <= $this->idleTimeoutSeconds) {
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
            $_SESSION[self::SESSION_PASSWORD_HASH_KEY],
            $_SESSION[self::SESSION_LAST_ACTIVITY_KEY],
            $_SESSION[self::SESSION_CREATED_AT_KEY],
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
