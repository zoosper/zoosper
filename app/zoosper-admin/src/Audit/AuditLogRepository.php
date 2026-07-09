<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit;

use PDO;

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

    /** @return list<array<string, mixed>> */
    public function latest(int $limit = 100): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_activity_log ORDER BY id DESC LIMIT :limit');
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
