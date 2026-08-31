<?php

declare(strict_types=1);

namespace Zoosper\Admin\Asset;

use InvalidArgumentException;

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
        /** @var list<string> */
        public array $screens = [],
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
            screens: self::screens($config['screens'] ?? []),
        );
    }

    /**
     * A missing screen context preserves the established diagnostic API and an
     * empty declaration means that the asset is global.
     */
    public function appliesTo(?string $screen): bool
    {
        return $screen === null || $this->screens === [] || in_array($screen, $this->screens, true);
    }

    /** @return list<string> */
    private static function screens(mixed $screens): array
    {
        if (!is_array($screens)) {
            throw new InvalidArgumentException('Admin asset screens must be an array.');
        }

        $normalised = [];
        foreach ($screens as $screen) {
            if (!is_string($screen) || trim($screen) === '') {
                throw new InvalidArgumentException('Admin asset screen names must be non-empty strings.');
            }

            $normalised[] = trim($screen);
        }

        return array_values(array_unique($normalised));
    }
}










