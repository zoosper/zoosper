<?php

declare(strict_types=1);

namespace Zoosper\Core\Grid;

/**
 * Declarative filter definition for the shared admin Grid engine.
 *
 * type = 'text'   -> free-text input, matched with LIKE %term% by the data source
 * type = 'select' -> dropdown; $options are [value, label] pairs
 */
final readonly class GridFilter
{
    /**
     * @param list<array{value: string, label: string}> $options
     */
    public function __construct(
        public string $key,
        public string $label,
        public string $type = 'text',
        public array $options = [],
    ) {
    }
}
