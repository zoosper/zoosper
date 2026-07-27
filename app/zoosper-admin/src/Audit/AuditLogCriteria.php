<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit;

use Zoosper\Core\Pagination\Pager;

/**
 * Search and pagination criteria for the admin activity log grid.
 *
 * Mirrors Zoosper\Page\Admin\PageGridCriteria's shape (Sonnet Phase 2 §4.2 asked
 * to reuse this existing, working pattern rather than invent a new one).
 */
final readonly class AuditLogCriteria
{
    public function __construct(
        public Pager $pager,
        public string $query = '',
        public string $entityType = '',
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
            entityType: trim((string) ($query['entity_type'] ?? '')),
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
        if ($this->entityType !== '') {
            $params['entity_type'] = $this->entityType;
        }

        return $params;
    }
}
