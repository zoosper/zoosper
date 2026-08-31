<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

/** Resolves proxy-derived request metadata only when the immediate peer is trusted. */
final readonly class TrustedProxyResolver
{
    /** @param list<string> $trustedProxies */
    public function __construct(private array $trustedProxies = [])
    {
    }

    public static function fromEnvironment(): self
    {
        $environmentValue = trim((string) ($_ENV['TRUSTED_PROXIES'] ?? ''));
        $processValue = getenv('TRUSTED_PROXIES');
        $raw = $environmentValue !== ''
            ? $environmentValue
            : trim($processValue === false ? '' : (string) $processValue);
        $trusted = array_values(array_filter(
            array_map('trim', explode(',', $raw)),
            static fn (string $value): bool => filter_var($value, FILTER_VALIDATE_IP) !== false,
        ));

        return new self(array_values(array_unique($trusted)));
    }

    /** @param array<string, mixed> $server */
    public function clientIp(array $server): ?string
    {
        $peer = $this->validIp($server['REMOTE_ADDR'] ?? null);
        if ($peer === null) {
            return null;
        }
        if (!$this->isTrusted($peer)) {
            return $peer;
        }

        $forwarded = explode(',', (string) ($server['HTTP_X_FORWARDED_FOR'] ?? ''));
        foreach ($forwarded as $candidate) {
            $ip = $this->validIp(trim($candidate));
            if ($ip !== null && !$this->isTrusted($ip)) {
                return $ip;
            }
        }

        return $peer;
    }

    /** @param array<string, mixed> $server */
    public function isHttps(array $server): bool
    {
        $https = strtolower((string) ($server['HTTPS'] ?? ''));
        if ($https !== '' && $https !== 'off') {
            return true;
        }
        if ((int) ($server['SERVER_PORT'] ?? 0) === 443) {
            return true;
        }

        $peer = $this->validIp($server['REMOTE_ADDR'] ?? null);
        return $peer !== null
            && $this->isTrusted($peer)
            && strtolower(trim(explode(',', (string) ($server['HTTP_X_FORWARDED_PROTO'] ?? ''))[0])) === 'https';
    }

    private function isTrusted(string $ip): bool
    {
        return in_array($ip, $this->trustedProxies, true);
    }

    private function validIp(mixed $value): ?string
    {
        $value = trim((string) $value);
        return filter_var($value, FILTER_VALIDATE_IP) !== false ? $value : null;
    }
}










