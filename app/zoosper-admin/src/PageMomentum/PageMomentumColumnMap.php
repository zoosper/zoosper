<?php

declare(strict_types=1);

namespace Zoosper\Admin\PageMomentum;

/**
 * Immutable mapping between the logical page-momentum fields and the actual
 * column names in a given `pages` table.
 *
 * Defaults match the Phase 1.86 assumptions, but every name can be overridden so
 * the same facts logic works against any real schema.
 */
final class PageMomentumColumnMap
{
    public function __construct(
        public readonly string $table = 'pages',
        public readonly string $status = 'status',
        public readonly string $title = 'title',
        public readonly string $publishedAt = 'published_at',
        public readonly string $updatedAt = 'updated_at',
        public readonly string $publishedValue = 'published',
    ) {
    }

    /**
     * Build a map from an associative array, falling back to defaults for any
     * omitted key. Unknown keys are ignored.
     *
     * @param array<string, string> $overrides
     */
    public static function fromArray(array $overrides): self
    {
        $defaults = new self();

        return new self(
            table: $overrides['table'] ?? $defaults->table,
            status: $overrides['status'] ?? $defaults->status,
            title: $overrides['title'] ?? $defaults->title,
            publishedAt: $overrides['published_at'] ?? $defaults->publishedAt,
            updatedAt: $overrides['updated_at'] ?? $defaults->updatedAt,
            publishedValue: $overrides['published_value'] ?? $defaults->publishedValue,
        );
    }

    /**
     * Validate that every configured identifier is a safe SQL identifier.
     *
     * @throws \InvalidArgumentException when an identifier is unsafe.
     */
    public function assertSafeIdentifiers(): void
    {
        foreach ([
            'table' => $this->table,
            'status' => $this->status,
            'title' => $this->title,
            'published_at' => $this->publishedAt,
            'updated_at' => $this->updatedAt,
        ] as $label => $identifier) {
            if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier) !== 1) {
                throw new \InvalidArgumentException(
                    "Unsafe SQL identifier for '{$label}': {$identifier}"
                );
            }
        }
    }
}
