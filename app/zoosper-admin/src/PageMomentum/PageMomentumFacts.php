<?php

declare(strict_types=1);

namespace Zoosper\Admin\PageMomentum;

/**
 * Immutable set of read-only facts powering the page momentum dashboard cards.
 *
 * This is a plain value object: it carries computed numbers and a small amount
 * of recent-activity context. It performs no I/O of its own.
 */
final class PageMomentumFacts
{
    /**
     * @param int         $totalPages            Total pages that exist.
     * @param int         $publishedPages        Pages currently published.
     * @param int         $draftPages            Pages currently in draft.
     * @param int         $publishedLast7Days    Pages published in the last 7 days.
     * @param int         $updatedLast7Days      Pages updated in the last 7 days.
     * @param string|null $mostRecentTitle       Title of the most recently updated page.
     * @param string|null $mostRecentUpdatedAt   Timestamp of the most recent update.
     */
    public function __construct(
        public readonly int $totalPages,
        public readonly int $publishedPages,
        public readonly int $draftPages,
        public readonly int $publishedLast7Days,
        public readonly int $updatedLast7Days,
        public readonly ?string $mostRecentTitle,
        public readonly ?string $mostRecentUpdatedAt,
    ) {
    }

    /**
     * Percentage (0-100, rounded) of pages that are published.
     */
    public function publishedShare(): int
    {
        if ($this->totalPages <= 0) {
            return 0;
        }

        return (int) round(($this->publishedPages / $this->totalPages) * 100);
    }

    /**
     * Render the facts as a plain array, convenient for views or JSON.
     *
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'total_pages' => $this->totalPages,
            'published_pages' => $this->publishedPages,
            'draft_pages' => $this->draftPages,
            'published_last_7_days' => $this->publishedLast7Days,
            'updated_last_7_days' => $this->updatedLast7Days,
            'published_share_percent' => $this->publishedShare(),
            'most_recent_title' => $this->mostRecentTitle,
            'most_recent_updated_at' => $this->mostRecentUpdatedAt,
        ];
    }
}
