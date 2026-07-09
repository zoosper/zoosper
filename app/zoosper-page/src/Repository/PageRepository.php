<?php

declare(strict_types=1);

namespace Zoosper\Page\Repository;

use PDO;
use RuntimeException;
use Zoosper\Page\Model\Page;

final readonly class PageRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return list<Page>
     */
    public function all(): array
    {
        $statement = $this->pdo->query(
            'SELECT * FROM pages ORDER BY updated_at DESC, id DESC'
        );

        return array_map(
            fn (array $row): Page => $this->hydrate($row),
            $statement->fetchAll(),
        );
    }

    public function findById(int $id): ?Page
    {
        $statement = $this->pdo->prepare('SELECT * FROM pages WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findPublishedBySlug(int $siteId, string $slug): ?Page
    {
        $statement = $this->pdo->prepare(
            'SELECT *
             FROM pages
             WHERE site_id = :site_id
               AND slug = :slug
               AND status = :status
             LIMIT 1'
        );
        $statement->execute([
            'site_id' => $siteId,
            'slug' => $slug,
            'status' => 'published',
        ]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function create(
        int $siteId,
        string $title,
        string $slug,
        string $content,
        string $status = 'draft',
        ?int $userId = null,
    ): int {
        if ($this->findAnyBySlug($siteId, $slug) !== null) {
            throw new RuntimeException('Page already exists for slug: ' . $slug);
        }

        $now = gmdate('Y-m-d H:i:s');
        $publishedAt = $status === 'published' ? $now : null;

        $statement = $this->pdo->prepare(
            'INSERT INTO pages (
                site_id,
                title,
                slug,
                content,
                status,
                meta_title,
                meta_description,
                published_at,
                created_by,
                updated_by,
                created_at,
                updated_at
             ) VALUES (
                :site_id,
                :title,
                :slug,
                :content,
                :status,
                :meta_title,
                :meta_description,
                :published_at,
                :created_by,
                :updated_by,
                :created_at,
                :updated_at
             )'
        );
        $statement->execute([
            'site_id' => $siteId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'status' => $status,
            'meta_title' => $title,
            'meta_description' => null,
            'published_at' => $publishedAt,
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $pageId = (int) $this->pdo->lastInsertId();
        $this->createRevision($pageId, $title, $content, $userId);

        return $pageId;
    }

    public function createPublished(
        int $siteId,
        string $title,
        string $slug,
        string $content,
        ?int $userId = null,
    ): int {
        return $this->create($siteId, $title, $slug, $content, 'published', $userId);
    }

    public function update(
        int $id,
        int $siteId,
        string $title,
        string $slug,
        string $content,
        ?int $userId = null,
    ): void {
        $existing = $this->findById($id);

        if ($existing === null) {
            throw new RuntimeException('Page does not exist: ' . $id);
        }

        $pageWithSlug = $this->findAnyBySlug($siteId, $slug);

        if ($pageWithSlug !== null && $pageWithSlug->id !== $id) {
            throw new RuntimeException('Another page already exists for slug: ' . $slug);
        }

        $now = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            'UPDATE pages
             SET site_id = :site_id,
                 title = :title,
                 slug = :slug,
                 content = :content,
                 meta_title = :meta_title,
                 updated_by = :updated_by,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'site_id' => $siteId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'meta_title' => $title,
            'updated_by' => $userId,
            'updated_at' => $now,
        ]);

        $this->createRevision($id, $title, $content, $userId);
    }

    public function publish(int $id, ?int $userId = null): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            'UPDATE pages
             SET status = :status,
                 published_at = COALESCE(published_at, :published_at),
                 updated_by = :updated_by,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'status' => 'published',
            'published_at' => $now,
            'updated_by' => $userId,
            'updated_at' => $now,
        ]);
    }

    public function unpublish(int $id, ?int $userId = null): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE pages
             SET status = :status,
                 updated_by = :updated_by,
                 updated_at = :updated_at
             WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'status' => 'draft',
            'updated_by' => $userId,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    private function findAnyBySlug(int $siteId, string $slug): ?Page
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM pages WHERE site_id = :site_id AND slug = :slug LIMIT 1'
        );
        $statement->execute([
            'site_id' => $siteId,
            'slug' => $slug,
        ]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    private function createRevision(int $pageId, string $title, string $content, ?int $userId): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO page_revisions (page_id, title, content, created_by, created_at)
             VALUES (:page_id, :title, :content, :created_by, :created_at)'
        );
        $statement->execute([
            'page_id' => $pageId,
            'title' => $title,
            'content' => $content,
            'created_by' => $userId,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Page
    {
        return new Page(
            id: (int) $row['id'],
            siteId: (int) $row['site_id'],
            title: (string) $row['title'],
            slug: (string) $row['slug'],
            content: (string) $row['content'],
            status: (string) $row['status'],
            metaTitle: $row['meta_title'] !== null ? (string) $row['meta_title'] : null,
            metaDescription: $row['meta_description'] !== null ? (string) $row['meta_description'] : null,
            publishedAt: $row['published_at'] !== null ? (string) $row['published_at'] : null,
        );
    }
}
