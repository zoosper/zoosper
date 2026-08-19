<?php

declare(strict_types=1);

namespace Zoosper\Mail\Config;

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;

/**
 * Resolves SMTP runtime configuration without exposing secrets to presentation code.
 *
 * Database-backed scope values win over project/runtime configuration. The
 * constructor remains backwards compatible: callers that provide only the
 * project ConfigRepository retain the pre-Phase-9F1 behaviour.
 */
final readonly class SmtpConfig
{
    public function __construct(
        private ConfigRepository $config,
        private ?ScopeConfigRepository $scoped = null,
        private ?ScopeContext $scope = null,
    ) {
    }

    public function transport(): string
    {
        return (string) $this->value('mail.default', 'smtp');
    }

    public function host(): string
    {
        return (string) $this->value('mail.smtp.host', '127.0.0.1');
    }

    public function port(): int
    {
        return (int) $this->value('mail.smtp.port', 1025);
    }

    public function username(): string
    {
        return (string) $this->value('mail.smtp.username', '');
    }

    /** Runtime-only secret accessor. Never expose this value to diagnostics or views. */
    public function password(): string
    {
        return (string) $this->value('mail.smtp.password', '');
    }

    public function encryption(): string
    {
        return strtolower((string) $this->value('mail.smtp.encryption', ''));
    }

    public function timeoutSeconds(): int
    {
        return max(1, (int) $this->value('mail.smtp.timeout_seconds', 15));
    }

    public function fromAddress(): string
    {
        return (string) $this->value('mail.from_address', 'no-reply@example.test');
    }

    public function fromName(): string
    {
        return (string) $this->value('mail.from_name', 'Zoosper');
    }

    private function value(string $path, string|int $default): string|int
    {
        $projectValue = $this->config->get($path, $default) ?? $default;
        if ($this->scoped === null) {
            return is_int($default) ? (int) $projectValue : (string) $projectValue;
        }

        $resolved = $this->scoped->get($path, $this->scope ?? ScopeContext::default(), (string) $projectValue);

        return is_int($default) ? (int) ($resolved ?? $default) : (string) ($resolved ?? $default);
    }
}
