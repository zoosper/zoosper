<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit;

use Zoosper\Core\Pagination\Pager;

/**
 * Search and pagination criteria for the admin login history grid.
 *
 * Mirrors Zoosper\Page\Admin\PageGridCriteria's shape (Sonnet Phase 2 §4.2).
 */
final readonly class LoginHistoryCriteria
{
    public function __construct(
        public Pager $pager,
        public string $query = '',
        public string $status = '',
    ) {
    }

    /**
     * @param array<string, mixed> $query Raw request query parameters.
     */
    public static function fromQuery(array $query): self
    {
        return new self(
            pager: Pager::fromQuery($query),
            query: trim((string) ($query['q'] ?? '')),
            status: trim((string) ($query['status'] ?? '')),
        );
    }

    /**
     * @return array<string, string|int>
     */
    public function linkParameters(): array
    {
        $params = [
            'page_size' => $this->pager->pageSize,
        ];

        if ($this->query !== '') {
            $params['q'] = $this->query;
        }
        if ($this->status !== '') {
            $params['status'] = $this->status;
        }

        return $params;
    }
}
