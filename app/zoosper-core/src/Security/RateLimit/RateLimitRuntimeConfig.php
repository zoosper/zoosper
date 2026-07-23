<?php

declare(strict_types=1);

namespace Zoosper\Core\Security\RateLimit;

/**
 * Immutable runtime configuration for rate limiting.
 */
final readonly class RateLimitRuntimeConfig
{
    /** @param array<string, RateLimitRule> $policies */
    public function __construct(
        public bool $enabled,
        public string $mode,
        public string $reportPath,
        public string $identitySalt,
        public array $policies,
    ) {
        if (! in_array($mode, ['report_only', 'enforce'], true)) {
            throw new \InvalidArgumentException('Rate limit mode must be report_only or enforce.');
        }
    }

    /** @param array<string,mixed> $config */
    public static function fromArray(array $config): self
    {
        $policies = [];
        foreach (($config['policies'] ?? []) as $key => $policy) {
            if (! is_array($policy)) {
                continue;
            }

            $policies[(string) $key] = new RateLimitRule(
                (string) $key,
                (int) ($policy['max_attempts'] ?? 1),
                (int) ($policy['window_seconds'] ?? 60),
                (string) ($policy['scope'] ?? 'default'),
            );
        }

        return new self(
            (bool) ($config['enabled'] ?? false),
            (string) ($config['mode'] ?? 'report_only'),
            (string) ($config['report_path'] ?? 'var/reports/rate-limit-events.jsonl'),
            (string) ($config['identity_salt'] ?? ''),
            $policies,
        );
    }

    public function isReportOnly(): bool
    {
        return $this->mode === 'report_only';
    }

    public function isEnforcing(): bool
    {
        return $this->enabled && $this->mode === 'enforce';
    }
}
