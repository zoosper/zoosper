<?php

declare(strict_types=1);

namespace Zoosper\Mail\Transport;

use Zoosper\Mail\Message\EmailMessage;

/**
 * Contract for outbound email transports.
 *
 * Implementations must not log SMTP passwords, message bodies, reset tokens,
 * OTPs, recovery codes, or other secrets. Audit-safe logs should contain only
 * recipient address, template/key if available, outcome and non-sensitive error
 * categories.
 */
interface MailerInterface
{
    /**
     * Send an email message.
     */
    public function send(EmailMessage $message): void;
}
