<?php

declare(strict_types=1);

namespace Zoosper\Mail\PasswordReset;

use Zoosper\Auth\PasswordReset\AdminPasswordResetDeliveryInterface;
use Zoosper\Auth\PasswordReset\AdminPasswordResetIssue;
use Zoosper\Mail\Config\SmtpConfig;
use Zoosper\Mail\Message\EmailAddress;
use Zoosper\Mail\Message\EmailMessage;
use Zoosper\Mail\Transport\SmtpMailer;

/** Sends reset credentials directly through SMTP, deliberately outside LoggedMailer. */
final readonly class SmtpAdminPasswordResetDelivery implements AdminPasswordResetDeliveryInterface
{
    public function __construct(private SmtpMailer $smtp, private SmtpConfig $config)
    {
    }

    public function deliver(AdminPasswordResetIssue $issue, string $absoluteResetUrl): void
    {
        $this->smtp->send(new EmailMessage(
            from: new EmailAddress(email: $this->config->fromAddress(), name: $this->config->fromName()),
            to: [new EmailAddress(email: $issue->email)],
            subject: 'Reset your Zoosper Admin password',
            textBody: "A password reset was requested for your Zoosper Admin account.\n\n"
                . "Reset your password: {$absoluteResetUrl}\n\n"
                . "This link expires at {$issue->expiresAt} UTC and can be used only once. "
                . "If you did not request this change, ignore this email.",
        ));
    }
}
