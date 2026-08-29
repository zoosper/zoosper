<?php

declare(strict_types=1);

namespace Zoosper\Admin\Theme;

use InvalidArgumentException;

/**
 * Immutable metadata for a trusted, module-declared Admin colour palette.
 *
 * Palettes contribute only identity and light/dark mode metadata here. Their
 * CSS remains an external CSP-safe asset declared through admin_assets.php.
 */
final readonly class AdminColourTheme
{
    public function __construct(
        public string $code,
        public string $name,
        public string $mode,
        public int $sortOrder = 100,
    ) {
        if (preg_match('/\A[a-z][a-z0-9-]{0,31}\z/', $this->code) !== 1) {
            throw new InvalidArgumentException('Admin colour theme code must be a lowercase identifier of at most 32 characters.');
        }

        if ($this->name === '' || trim($this->name) !== $this->name || strlen($this->name) > 80) {
            throw new InvalidArgumentException('Admin colour theme name must be a trimmed non-empty string of at most 80 bytes.');
        }

        if (!in_array($this->mode, ['light', 'dark'], true)) {
            throw new InvalidArgumentException('Admin colour theme mode must be light or dark.');
        }
    }

    /** @param array<string, mixed> $config */
    public static function fromConfig(string $code, array $config): self
    {
        $unknown = array_diff(array_keys($config), ['name', 'mode', 'sort_order']);
        if ($unknown !== [] || !is_string($config['name'] ?? null) || !is_string($config['mode'] ?? null)) {
            throw new InvalidArgumentException('Admin colour theme declaration must contain only string name, string mode, and optional integer sort_order.');
        }

        if (array_key_exists('sort_order', $config) && !is_int($config['sort_order'])) {
            throw new InvalidArgumentException('Admin colour theme sort_order must be an integer.');
        }

        return new self(
            code: $code,
            name: $config['name'],
            mode: $config['mode'],
            sortOrder: $config['sort_order'] ?? 100,
        );
    }
}
