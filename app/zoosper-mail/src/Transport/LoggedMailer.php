<?php

declare(strict_types=1);

namespace Zoosper\Mail\Transport;

use Throwable;
use Zoosper\Mail\Log\EmailLogRepository;
use Zoosper\Mail\Message\EmailMessage;

/**
 * Mailer decorator that records success/failure attempts in the SMTP email log.
 *
 * The logger stores message content for operational diagnostics. Do not send
 * TOTP secrets, OTP values, recovery-code plaintext, reset tokens, provisioning
 * URIs, SMTP passwords or payment data in mail bodies unless a later masking
 * policy protects those values before they are logged.
 */
final readonly class LoggedMailer implements MailerInterface
{
    public function __construct(private MailerInterface $inner, private EmailLogRepository $logs)
    {
    }

    public function send(EmailMessage $message): void
    {
        $messageUuid = bin2hex(random_bytes(16));

        try {
            $this->inner->send($message);
            $this->logs->recordSuccess($messageUuid, $message);
        } catch (Throwable $exception) {
            $this->logs->recordFailure($messageUuid, $message, $exception);
            throw $exception;
        }
    }
}
