<?php

declare(strict_types=1);

namespace Zoosper\Admin\PageMomentum;

/**
 * Computes read-only page momentum facts from a query contract.
 *
 * This class is pure and deterministic: given a query implementation it always
 * produces the same facts. It performs no I/O directly, which keeps it trivially
 * unit-testable and safe to call from a controller without touching the render
 * hot path.
 */
final class PageMomentumFactsProvider
{
    private const RECENT_WINDOW_DAYS = 7;

    public function __construct(
        private readonly PageMomentumQueryInterface $query,
    ) {
    }

    /**
     * Build the immutable facts snapshot for the dashboard cards.
     */
    public function facts(): PageMomentumFacts
    {
        $recent = $this->query->mostRecentlyUpdatedPage();

        return new PageMomentumFacts(
            totalPages: max(0, $this->query->countTotalPages()),
            publishedPages: max(0, $this->query->countPublishedPages()),
            draftPages: max(0, $this->query->countDraftPages()),
            publishedLast7Days: max(0, $this->query->countPublishedSince(self::RECENT_WINDOW_DAYS)),
            updatedLast7Days: max(0, $this->query->countUpdatedSince(self::RECENT_WINDOW_DAYS)),
            mostRecentTitle: $recent['title'] ?? null,
            mostRecentUpdatedAt: $recent['updated_at'] ?? null,
        );
    }
}
