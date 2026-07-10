<?php

declare(strict_types=1);

namespace Zoosper\Mail\Diagnostics;

use Zoosper\Mail\Config\SmtpConfig;

/**
 * Classifies the configured SMTP endpoint for operational diagnostics.
 *
 * This inspector never reads or prints SMTP passwords, message bodies, OTPs,
 * TOTP secrets, recovery-code plaintext, reset tokens or provisioning URIs. It
 * only describes whether the configured endpoint looks like a local catcher or
 * a real outbound SMTP service.
 */
final readonly class SmtpDeliveryModeInspector
{
    public function __construct(private SmtpConfig $config)
    {
    }

    /**
     * Return a safe delivery-mode label for diagnostics.
     */
    public function mode(): string
    {
        $host = strtolower($this->config->host());
        $port = $this->config->port();

        if (in_array($host, ['127.0.0.1', 'localhost', '::1'], true) && $port === 1025) {
            return 'local_mail_catcher';
        }

        if (in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
            return 'local_smtp';
        }

        return 'external_smtp';
    }

    /**
     * Return a human-readable explanation of the delivery mode.
     */
    public function explanation(): string
    {
        return match ($this->mode()) {
            'local_mail_catcher' => 'Configured SMTP endpoint looks like a local mail catcher such as Mailpit/MailHog. Messages are captured locally and are not delivered to the recipient inbox.',
            'local_smtp' => 'Configured SMTP endpoint is local. Delivery depends on the local SMTP service configuration.',
            default => 'Configured SMTP endpoint appears external. A successful send means the SMTP server accepted the message, but inbox delivery still depends on downstream mail routing, spam filtering and recipient mailbox systems.',
        };
    }
}
