<?php

declare(strict_types=1);

namespace Zoosper\TwoFactor\Service;

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Mail\Config\SmtpConfig;
use Zoosper\Mail\Message\EmailAddress;
use Zoosper\Mail\Message\EmailMessage;
use Zoosper\Mail\Transport\MailerInterface;

/**
 * Sends a safe admin 2FA setup notification email.
 *
 * This service deliberately sends only a notification and a link to the secure
 * Zoosper admin setup page. It must never include OTP values, TOTP secrets,
 * QR/provisioning URIs, recovery-code plaintext, reset tokens, SMTP passwords
 * or payment data in the email subject, body, headers or logs.
 */
final readonly class AdminTwoFactorSetupNotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private SmtpConfig $smtpConfig,
        private string $appUrl,
        private string $adminBasePath = '/admin',
    ) {
    }

    /**
     * Send a setup-required notification to an admin user.
     */
    public function sendSetupRequired(AdminUser $adminUser): void
    {
        $setupUrl = $this->adminUrl('/2fa/setup');
        $textBody = $this->textBody($adminUser, $setupUrl);
        $htmlBody = $this->htmlBody($adminUser, $setupUrl);

        $message = new EmailMessage(
            from: new EmailAddress($this->smtpConfig->fromAddress(), $this->smtpConfig->fromName()),
            to: [new EmailAddress($adminUser->email, $adminUser->name)],
            subject: 'Zoosper admin two-factor setup required',
            textBody: $textBody,
            htmlBody: $htmlBody,
        );

        $this->mailer->send($message);
    }

    /**
     * Build a safe plaintext notification body.
     */
    private function textBody(AdminUser $adminUser, string $setupUrl): string
    {
        return "Hello {$adminUser->name},\n\n"
            . "Your Zoosper admin account requires two-factor authentication setup.\n\n"
            . "Please sign in and complete setup from the secure admin page:\n"
            . $setupUrl . "\n\n"
            . "For security, this email does not include OTP codes, setup secrets, QR codes, recovery codes or reset tokens.\n\n"
            . "Zoosper";
    }

    /**
     * Build a safe HTML notification body.
     */
    private function htmlBody(AdminUser $adminUser, string $setupUrl): string
    {
        $name = htmlspecialchars($adminUser->name, ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($setupUrl, ENT_QUOTES, 'UTF-8');

        return '<p>Hello ' . $name . ',</p>'
            . '<p>Your Zoosper admin account requires two-factor authentication setup.</p>'
            . '<p><a href="' . $url . '">Complete 2FA setup</a></p>'
            . '<p>For security, this email does not include OTP codes, setup secrets, QR codes, recovery codes or reset tokens.</p>'
            . '<p>Zoosper</p>';
    }

    /**
     * Build an absolute or relative admin URL from app/admin config.
     */
    private function adminUrl(string $path): string
    {
        $relative = rtrim($this->adminBasePath, '/') . '/' . ltrim($path, '/');
        $base = rtrim($this->appUrl, '/');

        return $base !== '' ? $base . $relative : $relative;
    }
}
