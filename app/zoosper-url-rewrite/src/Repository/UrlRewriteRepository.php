<?php

declare(strict_types=1);

namespace Zoosper\UrlRewrite\Repository;

use PDO;
use Zoosper\UrlRewrite\Model\UrlRewrite;

/**
 * Repository for frontend URL rewrite records.
 *
 * This repository is intentionally scoped to simple request-path lookups for
 * Phase 0.24. Future phases can add admin CRUD, import/export and conflict
 * detection without changing frontend resolution code.
 */
final readonly class UrlRewriteRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Find an active rewrite for a site and request path.
     */
    public function findActiveByRequestPath(int $siteId, string $requestPath): ?UrlRewrite
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM url_rewrites WHERE site_id = :site_id AND request_path = :request_path AND is_active = 1 LIMIT 1'
        );
        $statement->execute([
            'site_id' => $siteId,
            'request_path' => trim($requestPath, '/'),
        ]);

        $row = $statement->fetch();
        return is_array($row) ? $this->hydrate($row) : null;
    }

    /**
     * Hydrate a URL rewrite model from a database row.
     *
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): UrlRewrite
    {
        return new UrlRewrite(
            id: (int) $row['id'],
            siteId: (int) $row['site_id'],
            requestPath: (string) $row['request_path'],
            targetPath: (string) $row['target_path'],
            entityType: (string) $row['entity_type'],
            entityId: isset($row['entity_id']) ? (int) $row['entity_id'] : null,
            redirectType: (int) $row['redirect_type'],
            isActive: (bool) $row['is_active'],
        );
    }
}
