<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit;

use PDO;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDataSourceInterface;
use Zoosper\Core\Pagination\PaginationResult;

/**
 * Repository for the admin activity log.
 *
 * Phase A (Grid Core): SUPERSEDES the bespoke AuditLogCriteria/paginate(
 * AuditLogCriteria) pair introduced in Phase 1.112 with the generic
 * GridDataSourceInterface. AuditLogCriteria.php has been deleted — one
 * criteria shape (GridCriteria) now serves every admin grid, per the decision
 * to avoid running two parallel pagination mechanisms side by side and to
 * keep the codebase lean.
 *
 * `latest()` remains for any other existing caller of the "top N" query.
 *
 * CORRECTNESS FIX (confirmed 2026-07-30, external reviewer pass):
 * paginate() previously hardcoded `ORDER BY id <direction>` and never
 * referenced $criteria->sortBy at all. This was harmless in practice ONLY
 * because this grid currently declares just one sortable column
 * ('created_at', mapped to the monotonic `id` column as a proxy — row ids
 * increase with time, avoiding ambiguity from duplicate timestamps). But
 * nothing enforced that a GridDataSourceInterface implementation actually
 * honours sortBy — if a third-party module ever contributed a different
 * sortable column via GridColumnRegistry, clicking that column header
 * would show an "active sort" UI indicator that silently did nothing.
 *
 * Fixed with an explicit, small allow-list mapping known sort keys to safe
 * column expressions (self::SORTABLE_COLUMNS), defaulting to `id` for any
 * unrecognised/null sortBy — deliberately NOT interpolating $criteria->sortBy
 * directly into SQL, which would be a real injection risk. Since the only
 * currently-declared sortable key ('created_at') still maps to 'id',
 * runtime behaviour for existing callers is completely unchanged; the
 * method now genuinely consults sortBy instead of silently ignoring it.
 */
final readonly class AuditLogRepository implements GridDataSourceInterface
{
    /**
     * Allow-list mapping known GridCriteria::$sortBy values to safe SQL
     * column expressions. Any sortBy not present here (including null)
     * falls back to 'id' — the existing default behaviour.
     *
     * @var array<string, string>
     */
    private const SORTABLE_COLUMNS = [
        'created_at' => 'id',
    ];

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
     * Original unbounded "top N" query, kept for any other existing caller.
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
     * GridDataSourceInterface implementation for the Audit Log grid.
     * Supports filtering by free-text `q` (summary/action/actor_email) and
     * `entity_type`, and sorting via the SORTABLE_COLUMNS allow-list above.
     *
     * @return PaginationResult<array<string, mixed>>
     */
    public function paginate(GridCriteria $criteria): PaginationResult
    {
        [$where, $params] = $this->whereClause($criteria);

        $count = $this->pdo->prepare('SELECT COUNT(*) FROM admin_activity_log ' . $where);
        foreach ($params as $name => $value) {
            $count->bindValue($name, $value);
        }
        $count->execute();
        $total = (int) $count->fetchColumn();

        $direction = strtoupper($criteria->sortDir) === 'ASC' ? 'ASC' : 'DESC';
        $column = self::SORTABLE_COLUMNS[$criteria->sortBy ?? ''] ?? 'id';
        $sql = 'SELECT * FROM admin_activity_log '
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
     * Delete activity log rows older than the given cutoff (retention).
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
    private function whereClause(GridCriteria $criteria): array
    {
        $conditions = [];
        $params = [];

        $query = $criteria->filters['q'] ?? '';
        if ($query !== '') {
            $conditions[] = '(summary LIKE :query OR action LIKE :query OR actor_email LIKE :query)';
            $params['query'] = '%' . $query . '%';
        }

        $entityType = $criteria->filters['entity_type'] ?? '';
        if ($entityType !== '') {
            $conditions[] = 'entity_type = :entity_type';
            $params['entity_type'] = $entityType;
        }

        return [$conditions === [] ? '' : 'WHERE ' . implode(' AND ', $conditions), $params];
    }
}

