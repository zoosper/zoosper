<?php

declare(strict_types=1);

namespace Zoosper\Admin\Asset;

/**
 * Immutable admin asset definition.
 *
 * Admin assets are declared by modules and rendered by the admin layout layer.
 * Asset definitions must not contain sensitive runtime values such as OTPs,
 * TOTP secrets, recovery codes, customer payment data, or session tokens.
 */
final readonly class AdminAsset
{
    public function __construct(
        public string $handle,
        public string $type,
        public string $path,
        public int $sortOrder = 100,
        public bool $defer = true,
    ) {
    }

    /**
     * Build an asset value object from module config.
     *
     * @param array<string, mixed> $config
     */
    public static function fromConfig(string $handle, array $config): self
    {
        return new self(
            handle: $handle,
            type: (string) ($config['type'] ?? 'script'),
            path: (string) ($config['path'] ?? ''),
            sortOrder: (int) ($config['sort_order'] ?? 100),
            defer: (bool) ($config['defer'] ?? true),
        );
    }
}
