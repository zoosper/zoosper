<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Core\Pagination\Pager;

/**
 * Search and filter criteria for the Pages admin grid.
 */
final readonly class PageGridCriteria
{
    public function __construct(
        public Pager $pager,
        public string $query = '',
        public string $status = '',
        public ?int $siteId = null,
    ) {
    }

    /**
     * Build criteria from request query parameters.
     *
     * @param array<string, mixed> $query Raw request query parameters.
     */
    public static function fromQuery(array $query): self
    {
        $siteId = isset($query['site_id']) && (int) $query['site_id'] > 0 ? (int) $query['site_id'] : null;

        return new self(
            pager: Pager::fromQuery($query),
            query: trim((string) ($query['q'] ?? '')),
            status: trim((string) ($query['status'] ?? '')),
            siteId: $siteId,
        );
    }

    /**
     * Query string values that should be preserved in pagination links.
     *
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
        if ($this->siteId !== null) {
            $params['site_id'] = $this->siteId;
        }

        return $params;
    }
}
