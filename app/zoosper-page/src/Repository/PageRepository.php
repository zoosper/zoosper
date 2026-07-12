<?php

declare(strict_types=1);

namespace Zoosper\Page\Repository;

use PDO;
use RuntimeException;
use Zoosper\Page\Model\Page;

/**
 * Repository for CMS pages.
 *
 * Phase 0.80 hydrates and preserves dual content metadata while keeping the
 * current HTML save/render behaviour unchanged.
 */
final readonly class PageRepository
{
    private bool $hasContentFormat;
    private bool $hasContentJson;

    public function __construct(private PDO $pdo)
    {
        $this->hasContentFormat = $this->columnExists('pages', 'content_format');
        $this->hasContentJson = $this->columnExists('pages', 'content_json');
    }

    public function create(int $siteId, string $title, string $slug, string $content, string $status = 'draft', ?int $userId = null): int
    {
        $columns = ['site_id', 'title', 'slug', 'content', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at'];
        $values = [':site_id', ':title', ':slug', ':content', ':status', ':created_by', ':updated_by', ':created_at', ':updated_at'];
        $params = [
            'site_id' => $siteId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'status' => $status,
            'created_by' => $userId,
            'updated_by' => $userId,
            'created_at' => gmdate('Y-m-d H:i:s'),
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ];

        if ($this->hasContentFormat) {
            $columns[] = 'content_format';
            $values[] = ':content_format';
            $params['content_format'] = 'html';
        }

        if ($this->hasContentJson) {
            $columns[] = 'content_json';
            $values[] = ':content_json';
            $params['content_json'] = null;
        }

        $sql = 'INSERT INTO pages (`' . implode('`, `', $columns) . '`) VALUES (' . implode(', ', $values) . ')';
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        $pageId = (int) $this->pdo->lastInsertId();
        $this->createRevision($pageId, $title, $content, $userId);

        return $pageId;
    }

    public function createPublished(int $siteId, string $title, string $slug, string $content, ?int $userId = null): int
    {
        return $this->create($siteId, $title, $slug, $content, 'published', $userId);
    }

    public function update(int $id, int $siteId, string $title, string $slug, string $content, ?int $userId = null): void
    {
        $sets = [
            '`site_id` = :site_id',
            '`title` = :title',
            '`slug` = :slug',
            '`content` = :content',
            '`updated_by` = :updated_by',
            '`updated_at` = :updated_at',
        ];
        $params = [
            'id' => $id,
            'site_id' => $siteId,
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'updated_by' => $userId,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ];

        if ($this->hasContentFormat) {
            $sets[] = '`content_format` = :content_format';
            $params['content_format'] = 'html';
        }

        if ($this->hasContentJson) {
            $sets[] = '`content_json` = :content_json';
            $params['content_json'] = null;
        }

        $statement = $this->pdo->prepare('UPDATE pages SET ' . implode(', ', $sets) . ' WHERE id = :id');
        $statement->execute($params);
        $this->createRevision($id, $title, $content, $userId);
    }

    public function findById(int $id): ?Page
    {
        $statement = $this->pdo->prepare('SELECT ' . $this->selectColumns() . ' FROM pages WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findPublishedBySlug(int $siteId, string $slug): ?Page
    {
        $statement = $this->pdo->prepare('SELECT ' . $this->selectColumns() . ' FROM pages WHERE site_id = :site_id AND slug = :slug AND status = :status LIMIT 1');
        $statement->execute(['site_id' => $siteId, 'slug' => $slug, 'status' => 'published']);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    /** @return list<Page> */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT ' . $this->selectColumns() . ' FROM pages ORDER BY id DESC');
        $pages = [];
        foreach ($statement->fetchAll() as $row) {
            if (is_array($row)) {
                $pages[] = $this->hydrate($row);
            }
        }

        return $pages;
    }

    public function publish(int $id, ?int $userId = null): void
    {
        $statement = $this->pdo->prepare('UPDATE pages SET status = :status, published_at = :published_at, updated_by = :updated_by, updated_at = :updated_at WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'status' => 'published',
            'published_at' => gmdate('Y-m-d H:i:s'),
            'updated_by' => $userId,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    public function unpublish(int $id, ?int $userId = null): void
    {
        $statement = $this->pdo->prepare('UPDATE pages SET status = :status, published_at = NULL, updated_by = :updated_by, updated_at = :updated_at WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'status' => 'draft',
            'updated_by' => $userId,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Page
    {
        return new Page(
            id: (int) $row['id'],
            siteId: (int) $row['site_id'],
            title: (string) $row['title'],
            slug: (string) $row['slug'],
            content: (string) $row['content'],
            status: (string) $row['status'],
            createdBy: isset($row['created_by']) ? (int) $row['created_by'] : null,
            updatedBy: isset($row['updated_by']) ? (int) $row['updated_by'] : null,
            createdAt: isset($row['created_at']) ? (string) $row['created_at'] : null,
            updatedAt: isset($row['updated_at']) ? (string) $row['updated_at'] : null,
            publishedAt: isset($row['published_at']) ? (string) $row['published_at'] : null,
            contentFormat: (string) ($row['content_format'] ?? 'html'),
            contentJson: isset($row['content_json']) && $row['content_json'] !== null ? (string) $row['content_json'] : null,
        );
    }

    private function selectColumns(): string
    {
        $columns = ['id', 'site_id', 'title', 'slug', 'content', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'published_at'];
        if ($this->hasContentFormat) {
            $columns[] = 'content_format';
        }
        if ($this->hasContentJson) {
            $columns[] = 'content_json';
        }

        return '`' . implode('`, `', $columns) . '`';
    }

    private function createRevision(int $pageId, string $title, string $content, ?int $userId): void
    {
        if (!$this->tableExists('page_revisions')) {
            return;
        }

        $statement = $this->pdo->prepare('INSERT INTO page_revisions (page_id, title, content, created_by, created_at) VALUES (:page_id, :title, :content, :created_by, :created_at)');
        $statement->execute([
            'page_id' => $pageId,
            'title' => $title,
            'content' => $content,
            'created_by' => $userId,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    private function columnExists(string $table, string $column): bool
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :column');
        $statement->execute(['table' => $table, 'column' => $column]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function tableExists(string $table): bool
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table');
        $statement->execute(['table' => $table]);

        return (int) $statement->fetchColumn() > 0;
    }
}
