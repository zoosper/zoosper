<?php

declare(strict_types=1);

namespace Zoosper\Mail\Config;

use Zoosper\Core\Config\ConfigRepository;

/**
 * Reads SMTP configuration without exposing secrets to logs or templates.
 *
 * The SMTP password is available only to the mail transport. It must never be
 * written to exceptions, audit events, debug output, mail logs or rendered
 * diagnostics.
 */
final readonly class SmtpConfig
{
    public function __construct(private ConfigRepository $config)
    {
    }

    public function host(): string
    {
        return (string) ($this->config->get('mail.smtp.host', '127.0.0.1') ?? '127.0.0.1');
    }

    public function port(): int
    {
        return (int) ($this->config->get('mail.smtp.port', 1025) ?? 1025);
    }

    public function username(): string
    {
        return (string) ($this->config->get('mail.smtp.username', '') ?? '');
    }

    public function password(): string
    {
        return (string) ($this->config->get('mail.smtp.password', '') ?? '');
    }

    public function encryption(): string
    {
        return strtolower((string) ($this->config->get('mail.smtp.encryption', '') ?? ''));
    }

    public function timeoutSeconds(): int
    {
        return max(1, (int) ($this->config->get('mail.smtp.timeout_seconds', 15) ?? 15));
    }

    public function fromAddress(): string
    {
        return (string) ($this->config->get('mail.from_address', 'no-reply@example.test') ?? 'no-reply@example.test');
    }

    public function fromName(): string
    {
        return (string) ($this->config->get('mail.from_name', 'Zoosper') ?? 'Zoosper');
    }
}
