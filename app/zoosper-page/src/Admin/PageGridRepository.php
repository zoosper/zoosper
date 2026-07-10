<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use PDO;
use Zoosper\Core\Pagination\PaginationResult;

/**
 * Query service for the Pages admin grid.
 *
 * This class is intentionally separate from the core page repository so the
 * admin grid can evolve filters, pagination and joins without polluting page
 * domain read/write operations.
 */
final readonly class PageGridRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Return a paginated set of page rows for the admin grid.
     *
     * @return PaginationResult<array<string, mixed>>
     */
    public function paginate(PageGridCriteria $criteria): PaginationResult
    {
        [$where, $params] = $this->whereClause($criteria);

        $count = $this->pdo->prepare('SELECT COUNT(*) FROM pages p ' . $where);
        foreach ($params as $name => $value) {
            $count->bindValue($name, $value);
        }
        $count->execute();
        $total = (int) $count->fetchColumn();

        $sql = 'SELECT p.*, s.name AS site_name
            FROM pages p
            LEFT JOIN sites s ON s.id = p.site_id
            ' . $where . '
            ORDER BY p.updated_at DESC, p.id DESC
            LIMIT :limit OFFSET :offset';

        $statement = $this->pdo->prepare($sql);
        foreach ($params as $name => $value) {
            $statement->bindValue($name, $value);
        }
        $statement->bindValue('limit', $criteria->pager->pageSize, PDO::PARAM_INT);
        $statement->bindValue('offset', $criteria->pager->offset(), PDO::PARAM_INT);
        $statement->execute();

        return new PaginationResult(
            items: $statement->fetchAll(),
            total: $total,
            page: $criteria->pager->page,
            pageSize: $criteria->pager->pageSize,
        );
    }

    /**
     * Build a safe WHERE clause and bound parameters for the page grid.
     *
     * @return array{0:string,1:array<string,string|int>}
     */
    private function whereClause(PageGridCriteria $criteria): array
    {
        $conditions = [];
        $params = [];

        if ($criteria->query !== '') {
            $conditions[] = '(p.title LIKE :query OR p.slug LIKE :query)';
            $params['query'] = '%' . $criteria->query . '%';
        }

        if ($criteria->status !== '') {
            $conditions[] = 'p.status = :status';
            $params['status'] = $criteria->status;
        }

        if ($criteria->siteId !== null) {
            $conditions[] = 'p.site_id = :site_id';
            $params['site_id'] = $criteria->siteId;
        }

        return [$conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions), $params];
    }
}
