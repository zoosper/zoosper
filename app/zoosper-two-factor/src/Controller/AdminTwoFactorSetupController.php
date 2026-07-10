<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Controller;

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;

/**
 * Admin 2FA setup controller.
 *
 * This controller displays sensitive setup material only during enrolment and
 * never writes secrets, OTPs, QR payloads or recovery-code plaintext to logs.
 */
final readonly class AdminTwoFactorSetupController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminLayout $layout,
        private AdminTwoFactorEnrollmentService $enrolment,
    ) {
    }

    public function form(Request $request): Response
    {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $setup = $this->enrolment->startSetup($user->email);
        $_SESSION['pending_2fa_secret'] = $setup['secret'];

        $html = '<div class="card"><h2>Set up two-factor authentication</h2>'
            . '<p class="muted">Add this setup key to your authenticator app, then enter the 6-digit code below.</p>'
            . '<p><strong>Setup key:</strong> <code>' . $this->e($setup['secret']) . '</code></p>'
            . '<p><strong>Authenticator URI:</strong> <code>' . $this->e($setup['uri']) . '</code></p>'
            . '<form method="post" action="/admin/2fa/setup">'
            . '<input type="hidden" name="_csrf_token" value="' . $this->e($this->csrf->token()) . '">'
            . '<label>Authenticator code <input type="text" name="otp" inputmode="numeric" autocomplete="one-time-code" required></label>'
            . '<div class="toolbar"><button type="submit">Confirm 2FA</button></div>'
            . '</form></div>';

        return Response::html($this->layout->render('Set up 2FA', $html, $user, 'admin-users'));
    }

    public function confirm(Request $request): Response
    {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $form = $request->form();
        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            return Response::html($this->layout->render('Set up 2FA', '<p class="error">Invalid security token.</p>', $user, 'admin-users'), 419);
        }

        $secret = (string) ($_SESSION['pending_2fa_secret'] ?? '');
        $codes = $secret !== '' ? $this->enrolment->confirm($user->id, $secret, (string) ($form['otp'] ?? '')) : [];
        if ($codes === []) {
            return Response::html($this->layout->render('Set up 2FA', '<p class="error">Invalid authenticator code.</p>', $user, 'admin-users'), 422);
        }

        unset($_SESSION['pending_2fa_secret']);
        $items = implode('', array_map(fn (string $code): string => '<li><code>' . $this->e($code) . '</code></li>', $codes));
        $html = '<div class="card"><h2>2FA enabled</h2><p>Save these recovery codes now. They will not be shown again.</p><ul>' . $items . '</ul><p><a class="button" href="/admin">Continue</a></p></div>';

        return Response::html($this->layout->render('2FA enabled', $html, $user, 'admin-users'));
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
