<?php

declare(strict_types=1);

namespace Zoosper\Admin\PageMomentum;

/**
 * Minimal read-only query contract required to compute page momentum facts.
 *
 * Implementations must only read. They must never mutate state. Keeping this
 * contract tiny lets the facts provider stay pure and fully unit-testable while
 * real implementations can be backed by a repository, PDO, or any data source.
 */
interface PageMomentumQueryInterface
{
    /**
     * Total number of pages that exist.
     */
    public function countTotalPages(): int;

    /**
     * Number of pages whose status is published.
     */
    public function countPublishedPages(): int;

    /**
     * Number of pages whose status is draft (i.e. not published).
     */
    public function countDraftPages(): int;

    /**
     * Number of pages published within the last $days days (inclusive).
     */
    public function countPublishedSince(int $days): int;

    /**
     * Number of pages updated within the last $days days (inclusive).
     */
    public function countUpdatedSince(int $days): int;

    /**
     * The most recently updated page as ['title' => string, 'updated_at' => string],
     * or null when there are no pages.
     *
     * @return array{title: string, updated_at: string}|null
     */
    public function mostRecentlyUpdatedPage(): ?array;
}
