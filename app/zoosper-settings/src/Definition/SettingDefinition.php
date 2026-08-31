<?php

declare(strict_types=1);

namespace Zoosper\Settings\Definition;

use InvalidArgumentException;

/** Immutable metadata for one admin-visible setting. */
final readonly class SettingDefinition
{
    /** @param list<string> $options */
    public function __construct(
        public string $path,
        public string $label,
        public string $type = 'text',
        public string $description = '',
        public mixed $default = null,
        public array $options = [],
        public bool $secret = false,
        public bool $readOnly = false,
        public int $sortOrder = 100,
    ) {
        if ($path === '' || !preg_match('/^[a-z][a-z0-9_.-]*$/', $path)) {
            throw new InvalidArgumentException("Invalid setting path: {$path}");
        }
        if ($label === '') {
            throw new InvalidArgumentException("Setting label is required for: {$path}");
        }
        if (!in_array($type, ['text', 'textarea', 'email', 'url', 'integer', 'decimal', 'boolean', 'select', 'multiselect', 'secret'], true)) {
            throw new InvalidArgumentException("Unsupported setting type '{$type}' for: {$path}");
        }
        if ($type === 'secret' && !$secret) {
            throw new InvalidArgumentException("Secret setting must declare secret=true: {$path}");
        }
    }
}










