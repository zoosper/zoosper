<?php

declare(strict_types=1);

namespace Zoosper\Core\Cache;

/**
 * Describes safe HTTP cache headers for public, private and no-store responses.
 *
 * Policies are provider-agnostic and work behind browser caches, reverse
 * proxies, Fastly, Cloudflare, MaxCDN-like CDNs or any other shared HTTP cache.
 * Sensitive responses must use no-store and must never contain OTPs, TOTP
 * secrets, recovery-code plaintext, reset tokens, SMTP passwords, payment data
 * or customer-private values in shared-cacheable responses.
 */
final readonly class HttpCachePolicy
{
    /**
     * @param array<string, string> $headers
     */
    private function __construct(public string $code, public array $headers)
    {
    }

    /**
     * Public full-page response suitable for shared cache when content is context-safe.
     */
    public static function publicPage(int $maxAgeSeconds = 300, int $sharedMaxAgeSeconds = 300): self
    {
        return new self('public_page', [
            'Cache-Control' => 'public, max-age=' . max(0, $maxAgeSeconds) . ', s-maxage=' . max(0, $sharedMaxAgeSeconds) . ', stale-while-revalidate=60',
            'X-Zoosper-Cache-Policy' => 'public_page',
        ]);
    }

    /**
     * Public AJAX fragment response for non-personalised fragments.
     */
    public static function publicFragment(int $maxAgeSeconds = 60, int $sharedMaxAgeSeconds = 60): self
    {
        return new self('public_fragment', [
            'Cache-Control' => 'public, max-age=' . max(0, $maxAgeSeconds) . ', s-maxage=' . max(0, $sharedMaxAgeSeconds) . ', stale-while-revalidate=30',
            'X-Zoosper-Cache-Policy' => 'public_fragment',
        ]);
    }

    /**
     * Private AJAX fragment response for user/session/customer-specific fragments.
     */
    public static function privateFragment(): self
    {
        return new self('private_fragment', [
            'Cache-Control' => 'private, no-cache',
            'X-Zoosper-Cache-Policy' => 'private_fragment',
        ]);
    }

    /**
     * Fully non-cacheable response for sensitive content.
     */
    public static function noStore(): self
    {
        return new self('no_store', [
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
            'X-Zoosper-Cache-Policy' => 'no_store',
        ]);
    }
}
