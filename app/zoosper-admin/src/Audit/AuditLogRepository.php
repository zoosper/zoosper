<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit;

use PDO;
use Zoosper\Core\Pagination\PaginationResult;

/**
 * Repository for the admin activity log.
 *
 * Phase 1.112 (Sonnet Phase 2 §4.2): adds real pagination + a retention helper,
 * mirroring Zoosper\Page\Admin\PageGridRepository's proven paginate() pattern
 * (COUNT query + LIMIT/OFFSET query, both parameter-bound). `latest()` is left
 * UNCHANGED so any existing caller keeps its current behaviour; `paginate()` is
 * the new entry point for the admin grid.
 */
final readonly class AuditLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @param array<string, mixed> $metadata */
    public function record(?int $adminUserId, ?string $actorEmail, string $action, string $entityType, ?string $entityId, string $summary, array $metadata, ?string $ipAddress, ?string $userAgent): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO admin_activity_log (admin_user_id, actor_email, action, entity_type, entity_id, summary, metadata_json, ip_address, user_agent, created_at)
             VALUES (:admin_user_id, :actor_email, :action, :entity_type, :entity_id, :summary, :metadata_json, :ip_address, :user_agent, :created_at)'
        );
        $statement->execute([
            'admin_user_id' => $adminUserId,
            'actor_email' => $actorEmail,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'summary' => $summary,
            'metadata_json' => json_encode($metadata, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Original unbounded "top N" query. UNCHANGED — kept for backward
     * compatibility with any existing caller.
     *
     * @return list<array<string, mixed>>
     */
    public function latest(int $limit = 100): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_activity_log ORDER BY id DESC LIMIT :limit');
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * Return a paginated, optionally filtered set of activity log rows.
     *
     * @return PaginationResult<array<string, mixed>>
     */
    public function paginate(AuditLogCriteria $criteria): PaginationResult
    {
        [$where, $params] = $this->whereClause($criteria);

        $count = $this->pdo->prepare('SELECT COUNT(*) FROM admin_activity_log ' . $where);
        foreach ($params as $name => $value) {
            $count->bindValue($name, $value);
        }
        $count->execute();
        $total = (int) $count->fetchColumn();

        $sql = 'SELECT * FROM admin_activity_log '
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
     * Delete activity log rows older than the given cutoff (retention).
     * Mirrors DatabaseRateLimitStore::deleteExpired()'s "delete before a cutoff,
     * return the count" shape. Read-write, so callers should use a dedicated
     * dry-run-first console command rather than calling this directly from
     * request-handling code.
     */
    public function deleteOlderThan(string $cutoff): int
    {
        $statement = $this->pdo->prepare('DELETE FROM admin_activity_log WHERE created_at < :cutoff');
        $statement->execute(['cutoff' => $cutoff]);

        return $statement->rowCount();
    }

    /**
     * @return array{0:string,1:array<string,string|int>}
     */
    private function whereClause(AuditLogCriteria $criteria): array
    {
        $conditions = [];
        $params = [];

        if ($criteria->query !== '') {
            $conditions[] = '(summary LIKE :query OR action LIKE :query OR actor_email LIKE :query)';
            $params['query'] = '%' . $criteria->query . '%';
        }

        if ($criteria->entityType !== '') {
            $conditions[] = 'entity_type = :entity_type';
            $params['entity_type'] = $criteria->entityType;
        }

        return [$conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions), $params];
    }
}
