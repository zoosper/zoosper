<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit;

use PDO;
use Zoosper\Core\Audit\LoginHistoryRecorderInterface;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDataSourceInterface;
use Zoosper\Pagination\PaginationResult;

/**
 * Repository for admin login history.
 *
 * Implements GridDataSourceInterface and LoginHistoryRecorderInterface for
 * logging and paginated display of admin login events with retention pruning.
 */
final readonly class LoginHistoryRepository implements GridDataSourceInterface, LoginHistoryRecorderInterface
{
    /**
     * @var array<string, string>
     */
    private const SORTABLE_COLUMNS = [
        'created_at' => 'id',
    ];

    public function __construct(private PDO $pdo)
    {
    }

    public function record(?int $adminUserId, string $email, string $status, ?string $ipAddress, ?string $userAgent): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_login_history (admin_user_id, email, status, ip_address, user_agent, created_at)
             VALUES (:admin_user_id, :email, :status, :ip_address, :user_agent, :created_at)'
        );
        $statement->execute([
            'admin_user_id' => $adminUserId,
            'email' => mb_strtolower($email),
            'status' => $status,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent !== null ? mb_substr($userAgent, 0, 500) : null,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Original unbounded "top N" query, kept for any other existing caller.
     *
     * @return list<array<string, mixed>>
     */
    public function latest(int $limit = 100): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_login_history ORDER BY id DESC LIMIT :limit');
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * GridDataSourceInterface implementation for the Login History grid.
     * Supports filtering by free-text `q` (email) and exact `status`, and
     * sorting via the SORTABLE_COLUMNS allow-list above.
     *
     * @return PaginationResult<array<string, mixed>>
     */
    public function paginate(GridCriteria $criteria): PaginationResult
    {
        [$where, $params] = $this->whereClause($criteria);

        $count = $this->pdo->prepare('SELECT COUNT(*) FROM admin_login_history ' . $where);
        foreach ($params as $name => $value) {
            $count->bindValue($name, $value);
        }
        $count->execute();
        $total = (int) $count->fetchColumn();

        $direction = strtoupper($criteria->sortDir) === 'ASC' ? 'ASC' : 'DESC';
        $column = self::SORTABLE_COLUMNS[$criteria->sortBy ?? ''] ?? 'id';
        $sql = 'SELECT * FROM admin_login_history '
            . $where
            . ' ORDER BY ' . $column . ' ' . $direction
            . ' LIMIT :limit OFFSET :offset';

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
     * Delete login history rows older than the given cutoff (retention).
     */
    public function deleteOlderThan(string $cutoff): int
    {
        $statement = $this->pdo->prepare('DELETE FROM admin_login_history WHERE created_at < :cutoff');
        $statement->execute(['cutoff' => $cutoff]);

        return $statement->rowCount();
    }

    /**
     * @return array{0:string,1:array<string,string|int>}
     */
    private function whereClause(GridCriteria $criteria): array
    {
        $conditions = [];
        $params = [];

        $query = $criteria->filters['q'] ?? '';
        if ($query !== '') {
            $conditions[] = 'email LIKE :query';
            $params['query'] = '%' . $query . '%';
        }

        $status = $criteria->filters['status'] ?? '';
        if ($status !== '') {
            $conditions[] = 'status = :status';
            $params['status'] = $status;
        }

        return [$conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions), $params];
    }
}

