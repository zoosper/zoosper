<?php

declare(strict_types=1);

namespace Zoosper\Mail\Diagnostics;

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Mail\Config\SmtpConfig;

/**
 * Produces redacted SMTP configuration diagnostics.
 *
 * The inspector must never expose SMTP passwords, email message bodies, reset
 * tokens, OTPs, TOTP secrets, recovery-code plaintext or provisioning URIs. It
 * only reports safe configuration metadata and validation warnings.
 */
final readonly class MailConfigurationInspector
{
    public function __construct(private ConfigRepository $config, private SmtpConfig $smtp)
    {
    }

    /**
     * Build a redacted configuration summary.
     */
    public function summary(): MailConfigurationSummary
    {
        return new MailConfigurationSummary(
            transport: (string) ($this->config->get('mail.default', 'smtp') ?? 'smtp'),
            host: $this->smtp->host(),
            port: $this->smtp->port(),
            username: $this->smtp->username(),
            passwordConfigured: $this->smtp->password() !== '',
            encryption: $this->smtp->encryption(),
            timeoutSeconds: $this->smtp->timeoutSeconds(),
            fromAddress: $this->smtp->fromAddress(),
            fromName: $this->smtp->fromName(),
        );
    }

    /**
     * Return non-secret configuration warnings.
     *
     * @return list<string>
     */
    public function warnings(): array
    {
        $warnings = [];

        if ($this->smtp->host() === '') {
            $warnings[] = 'SMTP host is empty.';
        }

        if ($this->smtp->port() <= 0 || $this->smtp->port() > 65535) {
            $warnings[] = 'SMTP port is outside the valid TCP port range.';
        }

        if (!in_array($this->smtp->encryption(), ['', 'tls', 'ssl'], true)) {
            $warnings[] = 'SMTP encryption should be empty, tls or ssl.';
        }

        if (!filter_var($this->smtp->fromAddress(), FILTER_VALIDATE_EMAIL)) {
            $warnings[] = 'MAIL_FROM_ADDRESS is not a valid email address.';
        }

        if ($this->smtp->username() !== '' && $this->smtp->password() === '') {
            $warnings[] = 'SMTP username is configured but SMTP password is empty.';
        }

        return $warnings;
    }
}
