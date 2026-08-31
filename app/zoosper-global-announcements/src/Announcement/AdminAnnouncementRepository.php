<?php

declare(strict_types=1);

namespace Zoosper\GlobalAnnouncements\Announcement;

use DateTimeImmutable;
use PDO;
use Zoosper\Core\Announcement\AdminAnnouncementProviderInterface;

final readonly class AdminAnnouncementRepository implements AdminAnnouncementProviderInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findById(int $id): ?AdminAnnouncement
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_announcements WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    /** @return list<AdminAnnouncement> */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM admin_announcements ORDER BY id DESC');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn (array $row): AdminAnnouncement => $this->hydrate($row), $rows);
    }

    public function findLatestPublished(): ?AdminAnnouncement
    {
        $statement = $this->pdo->prepare('SELECT * FROM admin_announcements WHERE status = :status ORDER BY published_at DESC, id DESC LIMIT 1');
        $statement->execute(['status' => 'published']);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findUnacknowledgedForUser(int $userId): ?AdminAnnouncement
    {
        $statement = $this->pdo->prepare(
            'SELECT a.* FROM admin_announcements a '
            . 'WHERE a.status = :status '
            . 'AND NOT EXISTS ('
            . '  SELECT 1 FROM admin_announcement_acknowledgments ack '
            . '  WHERE ack.announcement_id = a.id AND ack.admin_user_id = :user_id'
            . ') '
            . 'ORDER BY a.published_at DESC, a.id DESC '
            . 'LIMIT 1'
        );
        $statement->execute([
            'status' => 'published',
            'user_id' => $userId,
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function create(string $title, string $body, string $status = 'draft', ?int $createdByUserId = null): AdminAnnouncement
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $publishedAt = $status === 'published' ? $now : null;

        $statement = $this->pdo->prepare(
            'INSERT INTO admin_announcements (title, body, status, published_at, created_by_user_id, created_at, updated_at) '
            . 'VALUES (:title, :body, :status, :published_at, :created_by_user_id, :created_at, :updated_at)'
        );
        $statement->execute([
            'title' => trim($title),
            'body' => trim($body),
            'status' => $status,
            'published_at' => $publishedAt,
            'created_by_user_id' => $createdByUserId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return new AdminAnnouncement(
            id: $id,
            title: trim($title),
            body: trim($body),
            status: $status,
            publishedAt: $publishedAt !== null ? new DateTimeImmutable($publishedAt) : null,
            createdByUserId: $createdByUserId,
            createdAt: new DateTimeImmutable($now),
            updatedAt: new DateTimeImmutable($now),
        );
    }

    public function update(int $id, string $title, string $body, string $status): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $existing = $this->findById($id);
        $publishedAt = $existing?->publishedAt?->format('Y-m-d H:i:s');

        if ($status === 'published' && $publishedAt === null) {
            $publishedAt = $now;
        }

        $statement = $this->pdo->prepare(
            'UPDATE admin_announcements SET title = :title, body = :body, status = :status, published_at = :published_at, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'title' => trim($title),
            'body' => trim($body),
            'status' => $status,
            'published_at' => $publishedAt,
            'updated_at' => $now,
        ]);
    }

    public function publish(int $id): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            'UPDATE admin_announcements SET status = :status, published_at = :published_at, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'status' => 'published',
            'published_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function unpublish(int $id): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            'UPDATE admin_announcements SET status = :status, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'status' => 'draft',
            'updated_at' => $now,
        ]);
    }

    public function archive(int $id): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            'UPDATE admin_announcements SET status = :status, updated_at = :updated_at WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'status' => 'archived',
            'updated_at' => $now,
        ]);
    }

    public function acknowledge(int $announcementId, int $userId): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');

        // Idempotent duplicate-safe insert
        $statement = $this->pdo->prepare(
            'INSERT OR IGNORE INTO admin_announcement_acknowledgments (announcement_id, admin_user_id, acknowledged_at) '
            . 'VALUES (:announcement_id, :admin_user_id, :acknowledged_at)'
        );

        try {
            $statement->execute([
                'announcement_id' => $announcementId,
                'admin_user_id' => $userId,
                'acknowledged_at' => $now,
            ]);
        } catch (\PDOException) {
            // MySQL fallback for INSERT IGNORE vs ON DUPLICATE KEY UPDATE
            $mysqlStatement = $this->pdo->prepare(
                'INSERT INTO admin_announcement_acknowledgments (announcement_id, admin_user_id, acknowledged_at) '
                . 'VALUES (:announcement_id, :admin_user_id, :acknowledged_at) '
                . 'ON DUPLICATE KEY UPDATE acknowledged_at = VALUES(acknowledged_at)'
            );
            $mysqlStatement->execute([
                'announcement_id' => $announcementId,
                'admin_user_id' => $userId,
                'acknowledged_at' => $now,
            ]);
        }
    }

    public function isAcknowledged(int $announcementId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM admin_announcement_acknowledgments WHERE announcement_id = :announcement_id AND admin_user_id = :admin_user_id'
        );
        $statement->execute([
            'announcement_id' => $announcementId,
            'admin_user_id' => $userId,
        ]);

        return ((int) $statement->fetchColumn()) > 0;
    }

    /** @return array<int, int> */
    public function acknowledgmentCounts(): array
    {
        $statement = $this->pdo->query(
            'SELECT announcement_id, COUNT(*) AS total_count FROM admin_announcement_acknowledgments GROUP BY announcement_id'
        );
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['announcement_id']] = (int) $row['total_count'];
        }

        return $counts;
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): AdminAnnouncement
    {
        $publishedAt = $row['published_at'] ?? null;
        $createdAt = $row['created_at'] ?? null;
        $updatedAt = $row['updated_at'] ?? null;

        return new AdminAnnouncement(
            id: (int) $row['id'],
            title: (string) $row['title'],
            body: (string) $row['body'],
            status: (string) ($row['status'] ?? 'draft'),
            publishedAt: is_string($publishedAt) && $publishedAt !== '' ? new DateTimeImmutable($publishedAt) : null,
            createdByUserId: isset($row['created_by_user_id']) && $row['created_by_user_id'] !== null ? (int) $row['created_by_user_id'] : null,
            createdAt: is_string($createdAt) && $createdAt !== '' ? new DateTimeImmutable($createdAt) : null,
            updatedAt: is_string($updatedAt) && $updatedAt !== '' ? new DateTimeImmutable($updatedAt) : null,
        );
    }
}










