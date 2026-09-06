<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use Throwable;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Auth\PasswordReset\AdminPasswordResetDeliveryInterface;
use Zoosper\Auth\PasswordReset\AdminPasswordResetService;
use Zoosper\Auth\PasswordReset\AdminPasswordResetUrlBuilder;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;

/** Public, CSRF-protected Admin password-reset HTTP adapter. */
final readonly class PasswordResetController
{
    private const NEUTRAL_MESSAGE = 'If an active Admin account matches that email, a password reset link has been sent.';

    public function __construct(
        private AdminPasswordResetService $resets,
        private AdminPasswordResetUrlBuilder $urls,
        private AdminPasswordResetDeliveryInterface $delivery,
        private CsrfTokenManager $csrf,
        private AdminUrlGenerator $adminUrls,
        private ?AuditLoggerInterface $audit = null,
    ) {
    }

    public function forgotForm(Request $request): Response
    {
        return Response::html($this->page('Forgot password', $this->forgotPasswordForm()));
    }

    public function requestReset(Request $request): Response
    {
        $email = trim((string) ($request->form()['email'] ?? ''));
        try {
            $issue = $this->resets->issueForEmail($email);
            if ($issue !== null) {
                $this->delivery->deliver($issue, $this->urls->build($issue->token));
            }
        } catch (Throwable) {
            // Preserve the same public response for unknown, inactive, and delivery-failure cases.
        }
        return Response::html($this->page('Check your email', '<p class="notice notice-success" role="status">' . self::NEUTRAL_MESSAGE . '</p><p><a href="' . $this->e($this->adminUrls->url('login')) . '">Return to sign in</a></p>'));
    }

    public function resetForm(Request $request): Response
    {
        return Response::html($this->page('Choose a new password', $this->resetPasswordForm((string) $request->query('token', ''))));
    }

    public function resetPassword(Request $request): Response
    {
        $form = $request->form();
        $token = (string) ($form['token'] ?? '');
        $violations = $this->resets->reset($token, (string) ($form['password'] ?? ''), (string) ($form['password_confirmation'] ?? ''));
        if ($violations !== []) {
            return Response::html($this->page('Choose a new password', $this->resetPasswordForm($token, $violations)), 422);
        }
        $this->csrf->rotate();
        $this->audit?->logAction(null, null, 'admin.password_reset_completed', 'admin_user', null, 'Admin password reset completed.');
        return Response::redirect($this->adminUrls->url('login', ['reset' => 'complete']), 303);
    }

    private function forgotPasswordForm(): string
    {
        return '<p>Enter the email address for the Admin account.</p><form method="post" action="' . $this->e($this->adminUrls->url('forgot-password')) . '"><input type="hidden" name="_csrf_token" value="' . $this->e($this->csrf->token()) . '"><label>Email <input type="email" name="email" autocomplete="username" required autofocus></label><button type="submit">Send reset link</button></form><p><a href="' . $this->e($this->adminUrls->url('login')) . '">Return to sign in</a></p>';
    }

    /** @param list<string> $violations */
    private function resetPasswordForm(string $token, array $violations = []): string
    {
        $errors = $violations === [] ? '' : '<div class="notice notice-error" role="alert"><ul><li>' . implode('</li><li>', array_map($this->e(...), $violations)) . '</li></ul></div>';
        return $errors . '<form method="post" action="' . $this->e($this->adminUrls->url('reset-password')) . '"><input type="hidden" name="_csrf_token" value="' . $this->e($this->csrf->token()) . '"><input type="hidden" name="token" value="' . $this->e($token) . '"><label>New password <input type="password" name="password" autocomplete="new-password" required autofocus></label><label>Confirm new password <input type="password" name="password_confirmation" autocomplete="new-password" required></label><button type="submit">Reset password</button></form><p><a href="' . $this->e($this->adminUrls->url('login')) . '">Return to sign in</a></p>';
    }

    private function page(string $title, string $content): string
    {
        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>' . $this->e($title) . ' | Zoosper Admin</title><style>body{font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#f5f7fb;margin:0;display:grid;place-items:center;min-height:100vh}.login-card{background:#fff;border:1px solid #d8dee9;border-radius:14px;box-shadow:0 10px 30px rgba(15,23,42,.08);padding:28px;max-width:420px;width:92%;box-sizing:border-box}label{display:block;margin:14px 0}input{width:100%;box-sizing:border-box;padding:10px;border:1px solid #cbd5e1;border-radius:8px}button{margin-top:14px;width:100%;padding:11px;border:0;border-radius:8px;background:#0f172a;color:#fff;font-weight:700}.notice{padding:10px;border-radius:8px;margin-bottom:12px}.notice-error{background:#fee2e2;color:#991b1b}.notice-success{background:#dcfce7;color:#166534}a{color:#334155}</style></head><body><main class="login-card"><h1>' . $this->e($title) . '</h1>' . $content . '</main></body></html>';
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
