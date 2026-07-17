<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Controller;

use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\TwoFactor\Qr\TotpQrCodeSvgRenderer;
use Zoosper\TwoFactor\Service\AdminTwoFactorEnrollmentService;

/**
 * Admin 2FA setup controller.
 *
 * This controller displays sensitive setup material only during enrolment and
 * never writes secrets, OTPs, QR/provisioning payloads or recovery-code
 * plaintext to logs. The QR code is generated locally; external QR services
 * must not be used because the provisioning URI contains the TOTP secret.
 */
final readonly class AdminTwoFactorSetupController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminLayout $layout,
        private AdminTwoFactorEnrollmentService $enrolment,
        private TotpQrCodeSvgRenderer $qrCode,
        private string $adminBasePath = '/admin',
    ) {
    }

    /**
     * Show the 2FA setup form and a local SVG QR code when available.
     */
    public function form(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $setup = $this->enrolment->startSetup($user->email);
        $_SESSION['pending_2fa_secret'] = $setup['secret'];

        $qrMarkup = $this->qrMarkup($setup['uri']);
        $html = '<div class="card"><h2>Set up two-factor authentication</h2>'
            . '<p class="muted">Scan the QR code with your authenticator app, or enter the setup key manually. The setup key and QR code are shown only during enrolment.</p>'
            . $qrMarkup
            . '<p><strong>Setup key:</strong> <code>' . $this->e($setup['secret']) . '</code></p>'
            . '<details><summary>Show authenticator URI</summary><p><code>' . $this->e($setup['uri']) . '</code></p></details>'
            . '<form method="post" action="' . $this->e($this->adminUrl('/2fa/setup')) . '">'
            . '<input type="hidden" name="_csrf_token" value="' . $this->e($this->csrf->token()) . '">'
            . '<label>Authenticator code <input type="text" name="otp" inputmode="numeric" autocomplete="one-time-code" required></label>'
            . '<div class="toolbar"><button type="submit">Confirm 2FA</button></div>'
            . '</form></div>';

        return Response::html($this->layout->render('Set up 2FA', $html, $user, 'admin-users'));
    }

    /**
     * Confirm setup using the submitted one-time password.
     */
    public function confirm(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $form = $request->form();
$secret = (string) ($_SESSION['pending_2fa_secret'] ?? '');
        $codes = $secret !== '' ? $this->enrolment->confirm($user->id, $secret, (string) ($form['otp'] ?? '')) : [];
        if ($codes === []) {
            return Response::html($this->layout->render('Set up 2FA', '<p class="error">Invalid authenticator code.</p><p><a class="button secondary" href="' . $this->e($this->adminUrl('/2fa/setup')) . '">Try again</a></p>', $user, 'admin-users'), 422);
        }

        unset($_SESSION['pending_2fa_secret']);
        $items = implode('', array_map(fn (string $code): string => '<li><code>' . $this->e($code) . '</code></li>', $codes));
        $html = '<div class="card"><h2>2FA enabled</h2><p>Save these recovery codes now. They will not be shown again.</p><ul>' . $items . '</ul><p><a class="button" href="' . $this->e($this->adminUrl('')) . '">Continue</a></p></div>';

        return Response::html($this->layout->render('2FA enabled', $html, $user, 'admin-users'));
    }

    /**
     * Render a local QR code block, or a safe fallback when the dependency is missing.
     */
    private function qrMarkup(string $provisioningUri): string
    {
        if (!$this->qrCode->isAvailable()) {
            return '<div class="notice notice-warning">QR rendering dependency is not installed. Use the setup key below or run the QR dependency installer from this phase.</div>';
        }

        return '<div class="two-factor-qr" aria-label="Authenticator QR code">' . $this->qrCode->render($provisioningUri) . '</div>';
    }

    /**
     * Build an admin URL from the configured admin base path.
     */
    private function adminUrl(string $path): string
    {
        return rtrim($this->adminBasePath, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Escape text for safe HTML output.
     */
    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    /**
     * Return the authenticated admin user after the middleware authentication gate.
     */
    private function currentAdminUser(): \Zoosper\Auth\Model\AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new \RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}
