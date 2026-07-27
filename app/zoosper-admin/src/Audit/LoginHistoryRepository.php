<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit;

use PDO;
use Zoosper\Core\Pagination\PaginationResult;

/**
 * Repository for admin login history.
 *
 * Phase 1.112 (Sonnet Phase 2 §4.2): adds real pagination + a retention helper,
 * mirroring PageGridRepository::paginate()'s proven pattern. `latest()` is left
 * UNCHANGED for backward compatibility; `paginate()` is the new entry point.
 */
final readonly class LoginHistoryRepository
{
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
            'user_agent' => $userAgent,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Original unbounded "top N" query. UNCHANGED for backward compatibility.
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
     * Return a paginated, optionally filtered set of login history rows.
     *
     * @return PaginationResult<array<string, mixed>>
     */
    public function paginate(LoginHistoryCriteria $criteria): PaginationResult
    {
        [$where, $params] = $this->whereClause($criteria);

        $count = $this->pdo->prepare('SELECT COUNT(*) FROM admin_login_history ' . $where);
        foreach ($params as $name => $value) {
            $count->bindValue($name, $value);
        }
        $count->execute();
        $total = (int) $count->fetchColumn();

        $sql = 'SELECT * FROM admin_login_history '
            . $where
            . ' ORDER BY id DESC'
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
    private function whereClause(LoginHistoryCriteria $criteria): array
    {
        $conditions = [];
        $params = [];

        if ($criteria->query !== '') {
            $conditions[] = 'email LIKE :query';
            $params['query'] = '%' . $criteria->query . '%';
        }

        if ($criteria->status !== '') {
            $conditions[] = 'status = :status';
            $params['status'] = $criteria->status;
        }

        return [$conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions), $params];
    }
}
