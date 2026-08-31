<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Controller;

use Zoosper\Audit\Contract\LoginHistoryRecorderInterface;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiterInterface;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\TwoFactor\Challenge\TwoFactorChallengeResult;
use Zoosper\TwoFactor\Challenge\TwoFactorChallengeService;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;

/**
 * Login-time 2FA challenge (Sonnet Phase 2 §1 fix).
 *
 * Reachable only while the session is "pending 2FA" (password verified, second
 * factor not yet supplied). It renders a standalone page (NOT the admin layout,
 * since there is no fully-authenticated user yet), verifies a TOTP or recovery
 * code, and - only on success - promotes the session to fully authenticated.
 *
 * Phase 1.113 HOTFIX: successful 2FA completion NOW records a login-history
 * 'success' row (previously the ONLY recording happened in LoginController's
 * password-only branch, so an OTP-completed login was never logged at all).
 * Wrong-code attempts are also recorded as 'otp_failed' so failed authenticator
 * attempts are visible for security monitoring - directly serving the "detect
 * bad activity" use case. LoginHistoryRepository is an OPTIONAL, LAST
 * constructor parameter (the established drop-in-safe pattern in this
 * codebase): if not injected, recording is simply skipped rather than erroring.
 *
 * Never logs OTPs, TOTP secrets, recovery-code plaintext or challenge tokens.
 */
final readonly class AdminTwoFactorChallengeController
{
    private const TOKEN_KEY = 'pending_2fa_challenge_token';

    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private TwoFactorChallengeService $challenge,
        private AdminTwoFactorEnrollmentService $enrollment,
        private AdminUserRepository $users,
        private string $adminBasePath = '/admin',
        private ?LoginHistoryRecorderInterface $loginHistory = null,
        private ?AdminAuthenticationRateLimiterInterface $rateLimiter = null,
    ) {
    }

    /**
     * Show the challenge form, or bounce to login when no challenge is pending.
     */
    public function form(Request $request): Response
    {
        if ($this->guard->pendingTwoFactorUserId() === null) {
            return Response::redirect($this->path('/login'));
        }

        return Response::html($this->page($this->challengeForm()));
    }

    /**
     * Verify a submitted code and promote the session on success.
     */
    public function verify(Request $request): Response
    {
        $form = $request->form();

        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return Response::html($this->page($this->challengeForm('Your session security token expired. Please try again.')), 419);
        }

        $userId = $this->guard->pendingTwoFactorUserId();
        if ($userId === null) {
            return Response::redirect($this->path('/login'));
        }

        $decision = $this->rateLimiter?->checkTwoFactor($userId, $request->clientIp());
        if ($decision !== null && !$decision->allowed) {
            return Response::raw(
                '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Too many requests</title></head><body><main><h1>Too many verification attempts</h1><p>Please wait before trying again.</p></main></body></html>',
                429,
                ['Content-Type' => 'text/html; charset=utf-8', 'Retry-After' => (string) max(1, $decision->retryAfterSeconds), 'Cache-Control' => 'no-store'],
            );
        }

        $token = (string) ($_SESSION[self::TOKEN_KEY] ?? '');
        $code = trim((string) ($form['code'] ?? ''));
        $useRecovery = (string) ($form['mode'] ?? 'totp') === 'recovery';

        if ($useRecovery) {
            $result = $this->challenge->verifyRecoveryCode($token, $code);
        } else {
            $secret = $this->enrollment->revealSecret($userId);
            $result = $secret === null
                ? TwoFactorChallengeResult::invalidOrExpired()
                : $this->challenge->verifyTotp($token, $code, $secret);
        }

        if (!$result->passed) {
            if ($result->reason === 'invalid_or_expired') {
                $this->abandon();
                return Response::redirect($this->path('/login'));
            }

            // Phase 1.113: wrong-code attempts are now recorded so repeated
            // failures are visible in login history for security monitoring.
            $this->recordAttempt($request, $userId, 'otp_failed');

            return Response::html($this->page($this->challengeForm('Incorrect code. Please try again.')), 422);
        }

        $user = $this->users->findById($result->adminUserId);
        if ($user === null || !$this->guard->completeTwoFactorChallenge($user)) {
            $this->abandon();
            return Response::redirect($this->path('/login'));
        }

        unset($_SESSION[self::TOKEN_KEY]);
        $this->rateLimiter?->resetTwoFactor($user->id, $request->clientIp());
        $this->csrf->rotate();

        // Phase 1.113: THIS is the fix — a login that required 2FA now records
        // its success here, since LoginController's own login() never reaches
        // its "success" branch for enrolled users (it redirects here instead).
        $this->recordAttempt($request, $user->id, 'success', $user->email);

        return Response::redirect($this->adminBasePath);
    }

    /**
     * Clear any pending-2FA state (used on expiry / mismatch).
     */
    private function abandon(): void
    {
        unset($_SESSION[self::TOKEN_KEY]);
        $this->guard->clearPendingTwoFactorChallenge();
    }

    /**
     * Record a login-history row for a 2FA-stage event, when a
     * LoginHistoryRepository has been injected. Resolves the email from the
     * user id when not explicitly provided (e.g. on a wrong-code attempt, where
     * only the pending user id is known).
     */
    private function recordAttempt(Request $request, int $adminUserId, string $status, ?string $email = null): void
    {
        if ($this->loginHistory === null) {
            return;
        }

        if ($email === null) {
            $user = $this->users->findById($adminUserId);
            $email = $user?->email ?? '';
        }

        $this->loginHistory->record(
            $adminUserId,
            $email,
            $status,
            $request->clientIp(),
            $request->userAgent(),
        );
    }

    private function path(string $suffix): string
    {
        return rtrim($this->adminBasePath, '/') . $suffix;
    }

    private function challengeForm(?string $error = null): string
    {
        $token = $this->e($this->csrf->token());
        $action = $this->e($this->path('/2fa/challenge'));
        $errorHtml = $error !== null ? '<div class="notice notice-error">' . $this->e($error) . '</div>' : '';

        return <<<HTML
{$errorHtml}
<form method="post" action="{$action}" class="login-form">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <p>Enter the 6-digit code from your authenticator app.</p>
    <label>Authentication code <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus required></label>
    <label class="recovery-toggle"><input type="checkbox" name="mode" value="recovery"> Use a recovery code instead</label>
    <button type="submit">Verify</button>
</form>
HTML;
    }

    private function page(string $content): string
    {
        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Two-Factor Verification</title><style>body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f5f7fb;margin:0;display:grid;place-items:center;min-height:100vh}.login-card{background:#fff;border:1px solid #d8dee9;border-radius:14px;box-shadow:0 10px 30px rgba(15,23,42,.08);padding:28px;max-width:420px;width:92%}label{display:block;margin:14px 0}input[type=text]{width:100%;box-sizing:border-box;padding:10px;border:1px solid #cbd5e1;border-radius:8px;letter-spacing:.2em;font-size:1.1rem}.recovery-toggle{font-size:.9rem;color:#475569}.recovery-toggle input{width:auto;margin-right:6px}button{margin-top:14px;width:100%;padding:11px;border:0;border-radius:8px;background:#0f172a;color:#fff;font-weight:700}.notice{padding:10px;border-radius:8px;margin-bottom:12px}.notice-error{background:#fee2e2;color:#991b1b}</style></head><body><main class="login-card"><h1>Two-factor verification</h1>' . $content . '</main></body></html>';
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}










