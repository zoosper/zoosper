<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

final readonly class GridCompactFilterChipsRenderer
{
    /** @param array<string, scalar|array<mixed>> $filters */
    public function render(array $filters): string
    {
        $html = '<div class="grid-filter-chips" data-grid-filter-chips>';
        foreach ($filters as $key => $value) {
            $label = $key === 'site_id' ? 'Site' : ucwords(str_replace('_', ' ', $key));
            foreach ($this->flatten($value) as $item) {
                $html .= '<span class="grid-filter-chip">'
                    . $this->escape($label . ': ' . $item)
                    . '<button type="button" data-grid-remove-filter="' . $this->escape($key)
                    . '" aria-label="Remove ' . $this->escape($label) . ' filter">×</button></span>';
            }
        }
        return $html . '</div>';
    }

    /** @return list<string> */
    private function flatten(mixed $value): array
    {
        if (!is_array($value)) {
            return (string) $value === '' ? [] : [(string) $value];
        }
        $result = [];
        array_walk_recursive($value, static function (mixed $item) use (&$result): void {
            if ((string) $item !== '') $result[] = (string) $item;
        });
        return array_values(array_unique($result));
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
