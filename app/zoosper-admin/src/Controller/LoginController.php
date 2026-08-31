<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use Zoosper\Audit\LoginHistoryRepository;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiterInterface;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\TwoFactor\Challenge\TwoFactorChallengeService;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;
use Zoosper\TwoFactor\Service\AdminTwoFactorLoginRedirectService;

/**
 * Handles admin login and logout requests.
 *
 * Enforces CSRF validation, rate limiting, login event recording, and
 * two-factor challenge transitions when enrolled.
 *
 * This controller must never print, log, email or store OTPs, TOTP secrets,
 * QR/provisioning URIs, recovery-code plaintext, reset tokens, SMTP passwords or
 * payment data.
 */
final readonly class LoginController
{
    private const CHALLENGE_TOKEN_KEY = 'pending_2fa_challenge_token';

    public function __construct(
        private AuthService $auth,
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private LoginHistoryRepository $loginHistory,
        private ?AdminTwoFactorLoginRedirectService $twoFactorRedirect = null,
        private ?AdminTwoFactorEnrollmentService $twoFactorEnrollment = null,
        private ?TwoFactorChallengeService $twoFactorChallenge = null,
        private ?AdminUrlGenerator $adminUrls = null,
        private ?AdminAuthenticationRateLimiterInterface $rateLimiter = null,
    ) {
    }

    public function show(Request $request): Response
    {
        if ($this->guard->user() !== null) {
            return Response::redirect($this->adminUrl());
        }

        return Response::html($this->page($this->form()));
    }

    public function login(Request $request): Response
    {
        $form = $request->form();
        $token = (string) ($form['_csrf_token'] ?? '');
        if (!$this->csrf->isValid($token)) {
            return Response::html($this->page($this->form('Invalid security token.')), 419);
        }

        $email = trim((string) ($form['email'] ?? ''));
        $password = (string) ($form['password'] ?? '');
        $user = $this->auth->authenticate($email, $password);

        if ($user === null) {
            $this->recordLoginEvent($request, null, $email, 'failed');
            return Response::html($this->page($this->form('Invalid email or password.', $email)), 422);
        }

        $this->rateLimiter?->resetPasswordLogin($user->email, $request->clientIp());
        $this->csrf->rotate();

        // Enrolled users must pass a login-time 2FA challenge BEFORE the session is fully authenticated.
        if ($this->requiresTwoFactorChallenge($user)) {
            $this->guard->beginTwoFactorChallenge($user);
            $_SESSION[self::CHALLENGE_TOKEN_KEY] = $this->twoFactorChallenge->issue($user->id);

            $this->recordLoginEvent($request, $user->id, $user->email, 'password_ok_pending_2fa');

            return Response::redirect($this->adminUrl('2fa/challenge'));
        }

        // No active 2FA: full login now (redirect service nudges to setup).
        $this->guard->login($user);
        $this->recordLoginEvent($request, $user->id, $user->email, 'success');

        return Response::redirect($this->postLoginPath($user));
    }

    public function logout(Request $request): Response
    {
        $this->csrf->clear();
        $this->guard->logout();

        return Response::redirect($this->adminUrl('login'));
    }

    /**
     * True when the user has an active 2FA enrolment AND the challenge machinery
     * is wired. Falls back to the previous (password-only) behaviour otherwise,
     * so partial deployments never lock admins out.
     */
    private function requiresTwoFactorChallenge(AdminUser $user): bool
    {
        if ($this->twoFactorEnrollment === null || $this->twoFactorChallenge === null) {
            return false;
        }

        return !$this->twoFactorEnrollment->requiresEnrollment($user->id);
    }

    private function postLoginPath(AdminUser $user): string
    {
        if ($this->twoFactorRedirect === null) {
            return $this->adminUrl();
        }

        return $this->twoFactorRedirect->pathFor($user);
    }

    /**
     * Record a login-history event with client IP and user agent.
     */
    private function recordLoginEvent(Request $request, ?int $adminUserId, string $email, string $status): void
    {
        $this->loginHistory->record(
            $adminUserId,
            $email,
            $status,
            $request->clientIp(),
            $request->userAgent(),
        );
    }

    private function form(?string $error = null, string $email = ''): string
    {
        $token = $this->e($this->csrf->token());
        $email = $this->e($email);
        $errorHtml = $error !== null ? '<div class="notice notice-error">' . $this->e($error) . '</div>' : '';
        $action = $this->e($this->adminUrl('login'));

        return <<<HTML
{$errorHtml}
<form method="post" action="{$action}" class="login-form">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <label>Email <input type="email" name="email" value="{$email}" autocomplete="username" required autofocus></label>
<label>Password <input type="password" name="password" autocomplete="current-password" required></label>
    <button type="submit">Sign in</button>
</form>
HTML;
    }

    private function page(string $content): string
    {
        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Zoosper Admin Login</title><style>body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f5f7fb;margin:0;display:grid;place-items:center;min-height:100vh}.login-card{background:#fff;border:1px solid #d8dee9;border-radius:14px;box-shadow:0 10px 30px rgba(15,23,42,.08);padding:28px;max-width:420px;width:92%}label{display:block;margin:14px 0}input{width:100%;box-sizing:border-box;padding:10px;border:1px solid #cbd5e1;border-radius:8px}button{margin-top:14px;width:100%;padding:11px;border:0;border-radius:8px;background:#0f172a;color:#fff;font-weight:700}.notice{padding:10px;border-radius:8px;margin-bottom:12px}.notice-error{background:#fee2e2;color:#991b1b}</style></head><body><main class="login-card"><h1>Zoosper Admin</h1>' . $content . '</main></body></html>';
    }

    private function adminUrl(string $path = ''): string
    {
        if ($this->adminUrls !== null) {
            return $this->adminUrls->url($path);
        }

        return $path === '' ? '/admin' : '/admin/' . ltrim($path, '/');
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}










