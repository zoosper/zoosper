<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

/**
 * Regression guard proving that /admin/pages is served by the complete Grid
 * workspace rather than the retired legacy filter/table path.
 */
final class PageGridLiveMarkupContract
{
    /** @var list<string> */
    private const REQUIRED_MARKERS = [
        'data-grid-workspace',
        'data-grid-page-size',
        'data-grid-view-status',
        'data-grid-view-actions',
        'data-grid-export',
        'name="site_id[]"',
        'name="visible_columns[]"',
        'name="column_order[]"',
    ];

    /** @var list<string> */
    private const RETIRED_MARKERS = [
        '<span>Site ID</span><input type="text" name="site_id"',
        '<input type="hidden" name="page_size"',
    ];

    public static function assertComplete(string $html): void
    {
        $missing = array_values(array_filter(
            self::REQUIRED_MARKERS,
            static fn (string $marker): bool => !str_contains($html, $marker),
        ));
        $retired = array_values(array_filter(
            self::RETIRED_MARKERS,
            static fn (string $marker): bool => str_contains($html, $marker),
        ));

        if ($missing !== [] || $retired !== []) {
            throw new \RuntimeException(sprintf(
                'Pages Grid live cutover is incomplete. Missing: [%s]. Retired markup still present: [%s].',
                implode(', ', $missing),
                implode(', ', $retired),
            ));
        }
    }

    private function __construct()
    {
    }
}










