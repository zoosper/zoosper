<?php

declare(strict_types=1);

namespace Zoosper\Admin\Audit;

use PDO;

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

    /** @return list<array<string, mixed>> */
    public function latest(int $limit = 100): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_login_history ORDER BY id DESC LIMIT :limit');
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
