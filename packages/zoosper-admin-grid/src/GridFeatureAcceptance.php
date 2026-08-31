<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Evaluates the minimum contract required before a modern Grid is closed. */
final readonly class GridFeatureAcceptance
{
    /** @var array<string, string> */
    private const MARKERS = [
        'workspace' => 'data-grid-workspace',
        'filters_toggle' => 'data-grid-toggle="filters"',
        'columns_toggle' => 'data-grid-toggle="columns"',
        'page_size' => 'data-grid-page-size',
        'view_status' => 'grid-compact-status',
        'filter_chips' => 'data-grid-filter-chips',
        'export' => 'data-grid-export',
        'table' => 'grid-table',
    ];

    public function evaluate(string $gridKey, string $html): GridFeatureAcceptanceReport
    {
        $passed = [];
        $failed = [];

        foreach (self::MARKERS as $requirement => $marker) {
            if (str_contains($html, $marker)) {
                $passed[] = $requirement;
            } else {
                $failed[] = $requirement;
            }
        }

        if (str_contains($html, '<input type="hidden" name="page_size"')) {
            $failed[] = 'legacy_hidden_page_size_removed';
        } else {
            $passed[] = 'legacy_hidden_page_size_removed';
        }

        if (str_contains($html, '<input type="text" name="site_id"')) {
            $failed[] = 'legacy_site_id_input_removed';
        } else {
            $passed[] = 'legacy_site_id_input_removed';
        }

        return new GridFeatureAcceptanceReport($gridKey, $passed, $failed);
    }
}











