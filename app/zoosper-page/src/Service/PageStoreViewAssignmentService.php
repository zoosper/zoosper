<?php

declare(strict_types=1);

namespace Zoosper\Page\Service;

use Zoosper\Page\Repository\PageSiteAssignmentRepository;

/**
 * Coordinates page-to-site/store-view assignments from admin form data.
 *
 * The service accepts the modern `site_ids[]` tag-selector field and falls back
 * to the legacy single `site_id` value when needed. This keeps the page editor
 * safe during migration from single-site selection to tag-style multi-selection.
 */
final readonly class PageStoreViewAssignmentService
{
    public function __construct(private PageSiteAssignmentRepository $assignments)
    {
    }

    /**
     * Save selected site/store-view IDs for a page.
     *
     * @param array<string, mixed> $form Submitted admin form data.
     */
    public function saveFromForm(int $pageId, array $form): void
    {
        $siteIds = $this->extractSiteIds($form);
        if ($siteIds === []) {
            return;
        }

        $this->assignments->replaceForPage($pageId, $siteIds);
    }

    /**
     * Return chosen site IDs from a modern or legacy page form payload.
     *
     * @param array<string, mixed> $form Submitted admin form data.
     * @return list<int>
     */
    public function extractSiteIds(array $form): array
    {
        $rawValues = [];

        if (isset($form['site_ids']) && is_array($form['site_ids'])) {
            $rawValues = $form['site_ids'];
        } elseif (isset($form['site_id'])) {
            $rawValues = [$form['site_id']];
        }

        $siteIds = array_map(static fn (mixed $siteId): int => (int) $siteId, $rawValues);
        $siteIds = array_filter($siteIds, static fn (int $siteId): bool => $siteId > 0);

        return array_values(array_unique($siteIds));
    }

    /**
     * Return assigned IDs for editing, falling back to the current page site ID.
     *
     * @return list<int>
     */
    public function selectedSiteIds(int $pageId, ?int $fallbackSiteId = null): array
    {
        $selected = $this->assignments->siteIdsForPage($pageId);
        if ($selected !== []) {
            return $selected;
        }

        return $fallbackSiteId !== null && $fallbackSiteId > 0 ? [$fallbackSiteId] : [];
    }
}
