<?php

declare(strict_types=1);

namespace Zoosper\Core\Cache;

/**
 * Builds stable cache keys for pages, blocks and AJAX fragments.
 *
 * Cache keys must include all public dimensions that affect output. Private
 * fragments can include authentication/customer-group dimensions, but must not
 * include raw session IDs, CSRF tokens, OTPs, TOTP secrets, recovery-code
 * plaintext, reset tokens, SMTP passwords, payment data or customer-private
 * values.
 */
final readonly class CacheKeyBuilder
{
    public function __construct(private string $prefix = 'zoosper', private string $version = 'v1')
    {
    }

    /**
     * Build a context-aware full-page cache key.
     */
    public function page(CacheContext $context, string $identity = 'page'): string
    {
        return $this->build('page', $identity, $context->publicPageDimensions());
    }

    /**
     * Build a context-aware block cache key.
     *
     * @param array<string, scalar|null> $parameters
     */
    public function block(CacheContext $context, string $blockName, array $parameters = []): string
    {
        return $this->build('block', $blockName, $context->publicPageDimensions() + $this->normaliseParameters($parameters));
    }

    /**
     * Build a context-aware public AJAX fragment cache key.
     *
     * @param array<string, scalar|null> $parameters
     */
    public function publicFragment(CacheContext $context, string $fragmentName, array $parameters = []): string
    {
        return $this->build('fragment_public', $fragmentName, $context->publicPageDimensions() + $this->normaliseParameters($parameters));
    }

    /**
     * Build a private fragment key that includes only safe coarse user dimensions.
     *
     * @param array<string, scalar|null> $parameters
     */
    public function privateFragment(CacheContext $context, string $fragmentName, array $parameters = []): string
    {
        return $this->build('fragment_private', $fragmentName, $context->privateFragmentDimensions() + $this->normaliseParameters($parameters));
    }

    /**
     * Build a cache key from a namespace, identity and safe dimensions.
     *
     * @param array<string, string> $dimensions
     */
    private function build(string $namespace, string $identity, array $dimensions): string
    {
        ksort($dimensions);
        $parts = [$this->prefix, $this->version, $namespace, $this->segment($identity)];

        foreach ($dimensions as $key => $value) {
            $parts[] = $this->segment($key) . '=' . $this->segment($value);
        }

        return implode(':', $parts);
    }

    /**
     * @param array<string, scalar|null> $parameters
     * @return array<string, string>
     */
    private function normaliseParameters(array $parameters): array
    {
        $normalised = [];
        foreach ($parameters as $key => $value) {
            if ($value === null) {
                continue;
            }
            $normalised[(string) $key] = (string) $value;
        }

        return $normalised;
    }

    private function segment(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9_\-.\/]+/', '-', $value) ?? 'unknown';
        $value = trim($value, '-');

        return $value !== '' ? $value : 'default';
    }
}
