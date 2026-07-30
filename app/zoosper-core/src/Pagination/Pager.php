<?php

declare(strict_types=1);

namespace Zoosper\Core\Pagination;

/**
 * Normalises pagination request values for admin grids.
 *
 * The class deliberately clamps page size to a safe maximum so large admin
 * requests cannot accidentally pull an unbounded dataset into memory.
 *
 * SECURITY/PERFORMANCE FIX (confirmed 2026-07-30, external reviewer pass):
 * fromQuery() previously clamped page_size to a safe maximum, but placed
 * NO upper bound on page itself. A request like ?page=999999999 would
 * therefore reach offset() and produce a genuinely huge OFFSET value (e.g.
 * (999999999 - 1) * 20), which the database still has to scan through and
 * discard before returning any rows at all — a real cost-amplification
 * concern, especially against unbounded/ungoverned tables like
 * admin_activity_log, admin_login_history, and rate_limit_buckets (which
 * have no retention/cleanup yet, per a separate, still-open finding — this
 * fix reduces the blast radius of that gap, though it doesn't replace
 * adding real retention).
 *
 * Fixed with a new $maxPage parameter (mirroring the existing $maxPageSize
 * parameter's role exactly: a fixed safety ceiling, not a claim about how
 * many pages a given dataset actually has — Pager itself has no visibility
 * into total row counts at construction time, so this is deliberately a
 * generous, fixed guard against cost amplification, not a business-logic
 * decision about "valid" page numbers). Default of 100,000 is intentionally
 * far beyond what any legitimate admin grid pagination UI would ever
 * present as a real, clickable page link.
 */
final readonly class Pager
{
    public function __construct(
        public int $page,
        public int $pageSize,
    ) {
    }

    /**
     * Build a pager from raw query values.
     *
     * @param array<string, mixed> $query Request query parameters.
     */
    public static function fromQuery(array $query, int $defaultPageSize = 20, int $maxPageSize = 100, int $maxPage = 100_000): self
    {
        $requestedPage = max(1, (int) ($query['page'] ?? 1));
        $page = min($maxPage, $requestedPage);

        $requestedSize = (int) ($query['page_size'] ?? $defaultPageSize);
        $pageSize = max(1, min($maxPageSize, $requestedSize > 0 ? $requestedSize : $defaultPageSize));

        return new self($page, $pageSize);
    }

    public function offset(): int
    {
        return ($this->page - 1) * $this->pageSize;
    }
}
