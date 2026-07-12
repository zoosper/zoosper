<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use Throwable;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\TwoFactor\Service\AdminTwoFactorLoginRedirectService;

/**
 * Handles admin login and logout requests.
 *
 * After a successful password login this controller now delegates the post-login
 * destination to AdminTwoFactorLoginRedirectService. Admin users without active
 * 2FA are redirected to the secure setup page before they continue into the
 * admin dashboard. This controller must never print, log, email or store OTPs,
 * TOTP secrets, QR/provisioning URIs, recovery-code plaintext, reset tokens,
 * SMTP passwords or payment data.
 */
final readonly class LoginController
{
    public function __construct(
        private AuthService $auth,
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private LoginHistoryRepository $loginHistory,
        private ?AdminTwoFactorLoginRedirectService $twoFactorRedirect = null,
    ) {
    }

    /**
     * Show the admin login form.
     */
    public function show(Request $request): Response
    {
        if ($this->guard->user() !== null) {
            return Response::redirect('/admin');
        }

        return Response::html($this->page($this->form()));
    }

    /**
     * Authenticate an admin user and redirect to the appropriate post-login destination.
     */
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
            $this->recordLoginFailure($email);
            return Response::html($this->page($this->form('Invalid email or password.', $email)), 422);
        }

        $this->guard->login($user);
        $this->recordLoginSuccess($user);

        return Response::redirect($this->postLoginPath($user));
    }

    /**
     * Log out the current admin user.
     */
    public function logout(Request $request): Response
    {
        $this->guard->logout();

        return Response::redirect('/admin/login');
    }

    /**
     * Determine the safe post-login path.
     */
    private function postLoginPath(AdminUser $user): string
    {
        if ($this->twoFactorRedirect === null) {
            return '/admin';
        }

        return $this->twoFactorRedirect->pathFor($user);
    }

    /**
     * Best-effort login success recording without coupling to one repository method name.
     */
    private function recordLoginSuccess(AdminUser $user): void
    {
        $this->callLoginHistory(['recordSuccess', 'recordLoginSuccess', 'success'], [$user->id, $user->email]);
    }

    /**
     * Best-effort login failure recording without storing passwords or secrets.
     */
    private function recordLoginFailure(string $email): void
    {
        $this->callLoginHistory(['recordFailure', 'recordLoginFailure', 'failure'], [$email]);
    }

    /**
     * Try known LoginHistoryRepository method names and ignore incompatible signatures safely.
     *
     * @param list<string> $methods
     * @param list<mixed> $arguments
     */
    private function callLoginHistory(array $methods, array $arguments): void
    {
        foreach ($methods as $method) {
            if (!method_exists($this->loginHistory, $method)) {
                continue;
            }

            try {
                $this->loginHistory->{$method}(...$arguments);
                return;
            } catch (Throwable) {
                // Keep authentication stable even if historical repository method signatures changed.
            }
        }
    }

    /**
     * Render the admin login form.
     */
    private function form(?string $error = null, string $email = ''): string
    {
        $token = $this->e($this->csrf->token());
        $email = $this->e($email);
        $errorHtml = $error !== null ? '<div class="notice notice-error">' . $this->e($error) . '</div>' : '';

        return <<<HTML
{$errorHtml}
<form method="post" action="/admin/login" class="login-form">
    <input type="hidden" name="_csrf_token" value="{$token}">
    <label>Email <input type="email" name="email" value="{$email}" autocomplete="username" required autofocus></label>
<label>Password <input type="password" name="password" autocomplete="current-password" required></label>
    <button type="submit">Sign in</button>
</form>
HTML;
    }

    /**
     * Render a minimal admin login page without exposing runtime secrets.
     */
    private function page(string $content): string
    {
        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Zoosper Admin Login</title><style>body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f5f7fb;margin:0;display:grid;place-items:center;min-height:100vh}.login-card{background:#fff;border:1px solid #d8dee9;border-radius:14px;box-shadow:0 10px 30px rgba(15,23,42,.08);padding:28px;max-width:420px;width:92%}label{display:block;margin:14px 0}input{width:100%;box-sizing:border-box;padding:10px;border:1px solid #cbd5e1;border-radius:8px}button{margin-top:14px;width:100%;padding:11px;border:0;border-radius:8px;background:#0f172a;color:#fff;font-weight:700}.notice{padding:10px;border-radius:8px;margin-bottom:12px}.notice-error{background:#fee2e2;color:#991b1b}</style></head><body><main class="login-card"><h1>Zoosper Admin</h1>' . $content . '</main></body></html>';
    }

    /**
     * Escape a string for safe HTML output.
     */
    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
