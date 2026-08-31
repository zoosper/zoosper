<?php

declare(strict_types=1);

namespace Zoosper\AdminForm;

final readonly class AdminFormField
{
    /** @param array<string, mixed> $config */
    public function __construct(
        public string $name,
        public string $type,
        public string $label,
        public int $sortOrder,
        public string $section = 'default',
        public array $config = [],
    ) {
    }

    /** @param array<string, mixed> $config */
    public static function fromConfig(string $name, array $config): self
    {
        return new self(
            name: $name,
            type: (string) ($config['type'] ?? 'text'),
            label: (string) ($config['label'] ?? ucfirst(str_replace('_', ' ', $name))),
            sortOrder: (int) ($config['sort_order'] ?? 100),
            section: (string) ($config['section'] ?? 'default'),
            config: $config,
        );
    }
}











