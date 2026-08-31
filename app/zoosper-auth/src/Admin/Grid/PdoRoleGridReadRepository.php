<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use PDO;
use Zoosper\Pagination\PaginationResult;

/** PDO read model for the Roles Grid. */
final readonly class PdoRoleGridReadRepository implements RoleGridReadRepositoryInterface
{
    public function __construct(
        private PDO $pdo,
        private RoleGridSqlBuilder $sql,
    ) {
    }

    public function paginate(RoleGridCriteria $criteria): PaginationResult
    {
        $plan = $this->sql->build($criteria);

        $count = $this->pdo->prepare(
            'SELECT COUNT(*) FROM admin_roles r ' . $plan->whereSql,
        );
        $this->bindParameters($count, $plan->parameters);
        $count->execute();

        $statement = $this->pdo->prepare(
            'SELECT r.id, r.label, r.code '
            . 'FROM admin_roles r '
            . $plan->whereSql
            . ' ORDER BY ' . $plan->orderSql
            . ' LIMIT :grid_limit OFFSET :grid_offset',
        );
        $this->bindParameters($statement, $plan->parameters);
        $statement->bindValue(':grid_limit', $criteria->pager->pageSize, PDO::PARAM_INT);
        $statement->bindValue(':grid_offset', $criteria->pager->offset(), PDO::PARAM_INT);
        $statement->execute();

        return new PaginationResult(
            items: $statement->fetchAll(PDO::FETCH_ASSOC),
            total: (int) $count->fetchColumn(),
            page: $criteria->pager->page,
            pageSize: $criteria->pager->pageSize,
        );
    }

    /** @param array<string, string|int> $parameters */
    private function bindParameters(\PDOStatement $statement, array $parameters): void
    {
        foreach ($parameters as $name => $value) {
            $statement->bindValue(
                ':' . $name,
                $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR,
            );
        }
    }
}










