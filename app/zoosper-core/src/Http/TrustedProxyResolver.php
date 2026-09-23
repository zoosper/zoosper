<?php

declare(strict_types=1);

namespace Zoosper\Core\Http;

use InvalidArgumentException;

/** Resolves proxy-derived request metadata only when the immediate peer is trusted. */
final readonly class TrustedProxyResolver
{
    /** @var list<array{network: string, prefix: int, bytes: int}> */
    private array $trustedNetworks;

    /** @param list<string> $trustedProxies */
    public function __construct(array $trustedProxies = [])
    {
        $networks = [];
        foreach ($trustedProxies as $trustedProxy) {
            $trustedProxy = trim($trustedProxy);
            if ($trustedProxy === '') {
                continue;
            }
            $networks[$trustedProxy] = self::parseTrustedNetwork($trustedProxy);
        }
        $this->trustedNetworks = array_values($networks);
    }

    public static function fromEnvironment(): self
    {
        $environmentValue = trim((string) ($_ENV['TRUSTED_PROXIES'] ?? ''));
        $processValue = getenv('TRUSTED_PROXIES');
        $raw = $environmentValue !== ''
            ? $environmentValue
            : trim($processValue === false ? '' : (string) $processValue);

        return new self(array_map('trim', explode(',', $raw)));
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

        $forwarded = array_reverse(explode(',', (string) ($server['HTTP_X_FORWARDED_FOR'] ?? '')));
        foreach ($forwarded as $candidate) {
            $ip = $this->validIp($candidate);
            if ($ip === null) {
                return $peer;
            }
            if (!$this->isTrusted($ip)) {
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
        $packed = inet_pton($ip);
        if ($packed === false) {
            return false;
        }
        foreach ($this->trustedNetworks as $network) {
            if (strlen($packed) !== $network['bytes']) {
                continue;
            }
            if (self::prefixMatches($packed, $network['network'], $network['prefix'])) {
                return true;
            }
        }
        return false;
    }

    private function validIp(mixed $value): ?string
    {
        $value = trim((string) $value);
        if (filter_var($value, FILTER_VALIDATE_IP) === false) {
            return null;
        }
        $packed = inet_pton($value);
        if ($packed === false) {
            return null;
        }
        $normalised = inet_ntop($packed);
        return $normalised === false ? null : $normalised;
    }

    /** @return array{network: string, prefix: int, bytes: int} */
    private static function parseTrustedNetwork(string $entry): array
    {
        $parts = explode('/', $entry, 2);
        $address = $parts[0];
        $prefixValue = $parts[1] ?? null;
        if (filter_var($address, FILTER_VALIDATE_IP) === false) {
            throw new InvalidArgumentException('Invalid TRUSTED_PROXIES entry: ' . $entry);
        }
        $packed = inet_pton($address);
        if ($packed === false) {
            throw new InvalidArgumentException('Invalid TRUSTED_PROXIES entry: ' . $entry);
        }
        $bits = strlen($packed) * 8;
        if ($prefixValue === null) {
            $prefix = $bits;
        } elseif (!preg_match('/^(?:0|[1-9][0-9]*)$/', $prefixValue)) {
            throw new InvalidArgumentException('Invalid TRUSTED_PROXIES CIDR prefix: ' . $entry);
        } else {
            $prefix = (int) $prefixValue;
        }
        if ($prefix < 0 || $prefix > $bits) {
            throw new InvalidArgumentException('Invalid TRUSTED_PROXIES CIDR prefix: ' . $entry);
        }

        return ['network' => self::maskedNetwork($packed, $prefix), 'prefix' => $prefix, 'bytes' => strlen($packed)];
    }

    private static function prefixMatches(string $address, string $network, int $prefix): bool
    {
        return hash_equals(self::maskedNetwork($address, $prefix), $network);
    }

    private static function maskedNetwork(string $packed, int $prefix): string
    {
        $fullBytes = intdiv($prefix, 8);
        $remainingBits = $prefix % 8;
        $masked = $fullBytes === 0 ? '' : substr($packed, 0, $fullBytes);
        if ($remainingBits > 0) {
            $masked .= chr(ord($packed[$fullBytes]) & (0xFF << (8 - $remainingBits)));
        }
        return str_pad($masked, strlen($packed), "\0");
    }
}
