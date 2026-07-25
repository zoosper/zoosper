<?php

declare(strict_types=1);

namespace Zoosper\Admin\PageMomentum;

/**
 * Turns immutable page momentum facts into an ordered list of display-ready
 * cards for the dashboard. Pure formatting only: it performs no I/O and does
 * not depend on any framework, which keeps it trivially unit-testable.
 */
final class PageMomentumCardsPresenter
{
    public function __construct(
        private readonly PageMomentumFacts $facts,
    ) {
    }

    /**
     * Produce the dashboard cards in display order.
     *
     * Each card is: ['key' => string, 'label' => string, 'value' => string, 'hint' => string].
     *
     * @return list<array{key: string, label: string, value: string, hint: string}>
     */
    public function cards(): array
    {
        $facts = $this->facts;

        $mostRecent = $facts->mostRecentTitle !== null
            ? $facts->mostRecentTitle
            : 'No pages yet';

        $mostRecentHint = $facts->mostRecentUpdatedAt !== null
            ? 'Updated ' . $facts->mostRecentUpdatedAt
            : 'Nothing has been updated';

        return [
            [
                'key' => 'total_pages',
                'label' => 'Total pages',
                'value' => (string) $facts->totalPages,
                'hint' => 'All pages in the system',
            ],
            [
                'key' => 'published_pages',
                'label' => 'Published',
                'value' => (string) $facts->publishedPages,
                'hint' => $facts->publishedShare() . '% of all pages are live',
            ],
            [
                'key' => 'draft_pages',
                'label' => 'Drafts',
                'value' => (string) $facts->draftPages,
                'hint' => 'Not yet published',
            ],
            [
                'key' => 'published_last_7_days',
                'label' => 'Published (7 days)',
                'value' => (string) $facts->publishedLast7Days,
                'hint' => 'Went live in the last week',
            ],
            [
                'key' => 'updated_last_7_days',
                'label' => 'Updated (7 days)',
                'value' => (string) $facts->updatedLast7Days,
                'hint' => 'Edited in the last week',
            ],
            [
                'key' => 'most_recent',
                'label' => 'Most recent update',
                'value' => $mostRecent,
                'hint' => $mostRecentHint,
            ],
        ];
    }
}
