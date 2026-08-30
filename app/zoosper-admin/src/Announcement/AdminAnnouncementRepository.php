<?php

declare(strict_types=1);

namespace Zoosper\Admin\Announcement;

use DateTimeImmutable;
use PDO;

final readonly class AdminAnnouncementRepository
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

    public function delete(int $id): void
    {
        $stmtAck = $this->pdo->prepare('DELETE FROM admin_announcement_acknowledgments WHERE announcement_id = :id');
        $stmtAck->execute(['id' => $id]);

        $stmt = $this->pdo->prepare('DELETE FROM admin_announcements WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public function isAcknowledged(int $announcementId, int $userId): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM admin_announcement_acknowledgments WHERE announcement_id = :announcement_id AND admin_user_id = :user_id'
        );
        $statement->execute([
            'announcement_id' => $announcementId,
            'user_id' => $userId,
        ]);

        return ((int) $statement->fetchColumn()) > 0;
    }

    public function acknowledge(int $announcementId, int $userId): void
    {
        if ($this->isAcknowledged($announcementId, $userId)) {
            return;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO admin_announcement_acknowledgments (announcement_id, admin_user_id, acknowledged_at) '
            . 'VALUES (:announcement_id, :user_id, :acknowledged_at)'
        );
        $statement->execute([
            'announcement_id' => $announcementId,
            'user_id' => $userId,
            'acknowledged_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);
    }

    public function countAcknowledgments(int $announcementId): int
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM admin_announcement_acknowledgments WHERE announcement_id = :announcement_id'
        );
        $statement->execute(['announcement_id' => $announcementId]);

        return (int) $statement->fetchColumn();
    }

    /** @return array<int, int> [announcement_id => count] */
    public function acknowledgmentCounts(): array
    {
        $statement = $this->pdo->query(
            'SELECT announcement_id, COUNT(*) as cnt FROM admin_announcement_acknowledgments GROUP BY announcement_id'
        );
        $counts = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $counts[(int) $row['announcement_id']] = (int) $row['cnt'];
        }

        return $counts;
    }

    private function hydrate(array $row): AdminAnnouncement
    {
        return new AdminAnnouncement(
            id: (int) $row['id'],
            title: (string) $row['title'],
            body: (string) $row['body'],
            status: (string) ($row['status'] ?? 'draft'),
            publishedAt: isset($row['published_at']) && is_string($row['published_at']) && $row['published_at'] !== ''
                ? new DateTimeImmutable($row['published_at'])
                : null,
            createdByUserId: isset($row['created_by_user_id']) && $row['created_by_user_id'] !== null
                ? (int) $row['created_by_user_id']
                : null,
            createdAt: isset($row['created_at']) && is_string($row['created_at'])
                ? new DateTimeImmutable($row['created_at'])
                : null,
            updatedAt: isset($row['updated_at']) && is_string($row['updated_at'])
                ? new DateTimeImmutable($row['updated_at'])
                : null,
        );
    }
}
