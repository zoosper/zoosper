<?php

declare(strict_types=1);

namespace Zoosper\Grid;

/**
 * Declarative filter definition.
 *
 * Supported types are text, date, select and multiselect. Options may be legacy
 * value/label arrays or GridFilterOption objects.
 */
final readonly class GridFilter
{
    /** @param list<array{value: string, label: string}|GridFilterOption> $options */
    public function __construct(
        public string $key,
        public string $label,
        public string $type = 'text',
        public array $options = [],
    ) {
        if (!in_array($this->type, ['text', 'date', 'select', 'multiselect'], true)) {
            throw new \InvalidArgumentException('Unsupported grid filter type: ' . $this->type);
        }
    }

    /** @return list<GridFilterOption> */
    public function normalisedOptions(): array
    {
        return array_map(
            static fn (array|GridFilterOption $option): GridFilterOption => $option instanceof GridFilterOption
                ? $option
                : new GridFilterOption((string) $option['value'], (string) $option['label']),
            $this->options,
        );
    }
}











