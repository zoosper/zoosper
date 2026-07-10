<?php

declare(strict_types=1);

namespace Zoosper\Mail\Diagnostics;

/**
 * Redacted mail configuration summary for diagnostics output.
 *
 * This value object intentionally excludes SMTP passwords and message bodies.
 * It is safe to print in CLI diagnostics because it exposes only non-secret
 * connection metadata and whether a password value is configured.
 */
final readonly class MailConfigurationSummary
{
    public function __construct(
        public string $transport,
        public string $host,
        public int $port,
        public string $username,
        public bool $passwordConfigured,
        public string $encryption,
        public int $timeoutSeconds,
        public string $fromAddress,
        public string $fromName,
    ) {
    }

    /**
     * Return a safe array representation for CLI output.
     *
     * @return array<string, string|int|bool>
     */
    public function toArray(): array
    {
        return [
            'transport' => $this->transport,
            'host' => $this->host,
            'port' => $this->port,
            'username' => $this->username !== '' ? $this->username : '(not configured)',
            'password_configured' => $this->passwordConfigured,
            'encryption' => $this->encryption !== '' ? $this->encryption : '(none)',
            'timeout_seconds' => $this->timeoutSeconds,
            'from_address' => $this->fromAddress,
            'from_name' => $this->fromName,
        ];
    }
}
