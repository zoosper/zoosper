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

    public function createPublished(
        int $siteId,
        string $title,
        string $slug,
        string $content,
        ?int $userId = null,
    ): int {
        if ($this->findAnyBySlug($siteId, $slug) !== null) {
            throw new RuntimeException('Page already exists for slug: ' . $slug);
        }

        $now = gmdate('Y-m-d H:i:s');

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
            'status' => 'published',
            'meta_title' => $title,
            'meta_description' => null,
            'published_at' => $now,
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $pageId = (int) $this->pdo->lastInsertId();
        $this->createRevision($pageId, $title, $content, $userId);

        return $pageId;
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
