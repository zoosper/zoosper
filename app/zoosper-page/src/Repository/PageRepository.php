<?php

declare(strict_types=1);

namespace Zoosper\Page\Repository;

use PDO;
use Zoosper\Page\Model\Page;

/**
 * Repository for CMS pages.
 *
 * The repository hydrates dual content metadata (`content_format`,
 * `content_json`) and SEO metadata while keeping the current HTML save/render
 * behaviour unchanged. Optional-column detection is driver-aware so the same
 * code works on MySQL (INFORMATION_SCHEMA) and SQLite (PRAGMA/sqlite_master),
 * keeping the project's sanctioned sqlite-for-local-dev policy working.
 */
final readonly class PageRepository
{
    private bool $hasContentFormat;
    private bool $hasContentJson;
    private bool $hasMetaTitle;
    private bool $hasMetaDescription;
    private bool $hasMetaKeywords;
    private bool $hasCanonicalUrl;

    public function __construct(private PDO $pdo)
    {
        $this->hasContentFormat = $this->columnExists('pages', 'content_format');
        $this->hasContentJson = $this->columnExists('pages', 'content_json');
        $this->hasMetaTitle = $this->columnExists('pages', 'meta_title');
        $this->hasMetaDescription = $this->columnExists('pages', 'meta_description');
        $this->hasMetaKeywords = $this->columnExists('pages', 'meta_keywords');
        $this->hasCanonicalUrl = $this->columnExists('pages', 'canonical_url');
    }

    public function create(
        int $siteId,
        string $title,
        string $slug,
        string $content,
        string $status = 'draft',
        ?int $userId = null,
        string $contentFormat = 'html',
        ?string $contentJson = null,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?string $metaKeywords = null,
        ?string $canonicalUrl = null,
    ): int {
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

        $this->addOptionalCreateColumn($columns, $values, $params, 'content_format', $contentFormat, $this->hasContentFormat);
        $this->addOptionalCreateColumn($columns, $values, $params, 'content_json', $contentJson, $this->hasContentJson);
        $this->addOptionalCreateColumn($columns, $values, $params, 'meta_title', $this->normaliseNullable($metaTitle), $this->hasMetaTitle);
        $this->addOptionalCreateColumn($columns, $values, $params, 'meta_description', $this->normaliseNullable($metaDescription), $this->hasMetaDescription);
        $this->addOptionalCreateColumn($columns, $values, $params, 'meta_keywords', $this->normaliseNullable($metaKeywords), $this->hasMetaKeywords);
        $this->addOptionalCreateColumn($columns, $values, $params, 'canonical_url', $this->normaliseNullable($canonicalUrl), $this->hasCanonicalUrl);

        $statement = $this->pdo->prepare('INSERT INTO pages (`' . implode('`, `', $columns) . '`) VALUES (' . implode(', ', $values) . ')');
        $statement->execute($params);

        $pageId = (int) $this->pdo->lastInsertId();

        return $pageId;
    }

    public function createPublished(
        int $siteId,
        string $title,
        string $slug,
        string $content,
        ?int $userId = null,
        string $contentFormat = 'html',
        ?string $contentJson = null,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?string $metaKeywords = null,
        ?string $canonicalUrl = null,
    ): int {
        return $this->create(
            siteId: $siteId,
            title: $title,
            slug: $slug,
            content: $content,
            status: 'published',
            userId: $userId,
            contentFormat: $contentFormat,
            contentJson: $contentJson,
            metaTitle: $metaTitle,
            metaDescription: $metaDescription,
            metaKeywords: $metaKeywords,
            canonicalUrl: $canonicalUrl,
        );
    }

    public function update(
        int $id,
        int $siteId,
        string $title,
        string $slug,
        string $content,
        ?int $userId = null,
        string $contentFormat = 'html',
        ?string $contentJson = null,
        ?string $metaTitle = null,
        ?string $metaDescription = null,
        ?string $metaKeywords = null,
        ?string $canonicalUrl = null,
    ): void {
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

        $this->addOptionalUpdateColumn($sets, $params, 'content_format', $contentFormat, $this->hasContentFormat);
        $this->addOptionalUpdateColumn($sets, $params, 'content_json', $contentJson, $this->hasContentJson);
        $this->addOptionalUpdateColumn($sets, $params, 'meta_title', $this->normaliseNullable($metaTitle), $this->hasMetaTitle);
        $this->addOptionalUpdateColumn($sets, $params, 'meta_description', $this->normaliseNullable($metaDescription), $this->hasMetaDescription);
        $this->addOptionalUpdateColumn($sets, $params, 'meta_keywords', $this->normaliseNullable($metaKeywords), $this->hasMetaKeywords);
        $this->addOptionalUpdateColumn($sets, $params, 'canonical_url', $this->normaliseNullable($canonicalUrl), $this->hasCanonicalUrl);

        $statement = $this->pdo->prepare('UPDATE pages SET ' . implode(', ', $sets) . ' WHERE id = :id');
        $statement->execute($params);
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
    /** @return list<Page> */
    public function allPublishedForSite(int $siteId): array
    {
        $statement = $this->pdo->prepare('SELECT ' . $this->selectColumns() . ' FROM pages WHERE site_id = :site_id AND status = :status ORDER BY slug ASC, id ASC');
        $statement->execute(['site_id' => $siteId, 'status' => 'published']);
        return array_map(fn (array $row): Page => $this->hydrate($row), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @return list<Page> */
    public function allForSite(int $siteId): array
    {
        $statement = $this->pdo->prepare('SELECT ' . $this->selectColumns() . ' FROM pages WHERE site_id = :site_id ORDER BY id DESC');
        $statement->execute(['site_id' => $siteId]);
        return array_map(fn (array $row): Page => $this->hydrate($row), $statement->fetchAll(PDO::FETCH_ASSOC));
    }

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

    public function archive(int $id, ?int $userId = null): void
    {
        $statement = $this->pdo->prepare('UPDATE pages SET status=:status, published_at=NULL, updated_by=:updated_by, updated_at=:updated_at WHERE id=:id');
        $statement->execute(['id'=>$id,'status'=>'archived','updated_by'=>$userId,'updated_at'=>gmdate('Y-m-d H:i:s')]);
    }
    public function restoreArchived(int $id, ?int $userId = null): void
    {
        $statement = $this->pdo->prepare('UPDATE pages SET status=:status, published_at=NULL, updated_by=:updated_by, updated_at=:updated_at WHERE id=:id AND status=:archived');
        $statement->execute(['id'=>$id,'status'=>'draft','archived'=>'archived','updated_by'=>$userId,'updated_at'=>gmdate('Y-m-d H:i:s')]);
    }
    public function deletePermanently(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM pages WHERE id=:id');
        $statement->execute(['id'=>$id]);
        if ($statement->rowCount() !== 1) { throw new \RuntimeException('Page was not deleted.'); }
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
            contentJson: $this->nullableString($row['content_json'] ?? null),
            metaTitle: $this->nullableString($row['meta_title'] ?? null),
            metaDescription: $this->nullableString($row['meta_description'] ?? null),
            metaKeywords: $this->nullableString($row['meta_keywords'] ?? null),
            canonicalUrl: $this->nullableString($row['canonical_url'] ?? null),
        );
    }

    private function selectColumns(): string
    {
        $columns = ['id', 'site_id', 'title', 'slug', 'content', 'status', 'created_by', 'updated_by', 'created_at', 'updated_at', 'published_at'];

        $this->addOptionalSelectColumn($columns, 'content_format', $this->hasContentFormat);
        $this->addOptionalSelectColumn($columns, 'content_json', $this->hasContentJson);
        $this->addOptionalSelectColumn($columns, 'meta_title', $this->hasMetaTitle);
        $this->addOptionalSelectColumn($columns, 'meta_description', $this->hasMetaDescription);
        $this->addOptionalSelectColumn($columns, 'meta_keywords', $this->hasMetaKeywords);
        $this->addOptionalSelectColumn($columns, 'canonical_url', $this->hasCanonicalUrl);

        return '`' . implode('`, `', $columns) . '`';
    }

    public function restoreRevision(int $siteId, \Zoosper\Page\Model\PageRevision $revision, ?int $userId): void
    {
        $this->update(
            id: $revision->pageId,
            siteId: $siteId,
            title: $revision->title,
            slug: $revision->slug,
            content: $revision->content,
            userId: $userId,
            contentFormat: $revision->contentFormat,
            contentJson: $revision->contentJson,
            metaTitle: $revision->metaTitle,
            metaDescription: $revision->metaDescription,
            metaKeywords: $revision->metaKeywords,
            canonicalUrl: $revision->canonicalUrl,
        );
        $revision->status === 'published'
            ? $this->publish($revision->pageId, $userId)
            : $this->unpublish($revision->pageId, $userId);
    }

    /** @param list<string> $columns @param array<string, mixed> $params */
    private function addOptionalCreateColumn(array &$columns, array &$values, array &$params, string $column, mixed $value, bool $enabled): void
    {
        if (!$enabled) {
            return;
        }

        $columns[] = $column;
        $values[] = ':' . $column;
        $params[$column] = $value;
    }

    /** @param list<string> $sets @param array<string, mixed> $params */
    private function addOptionalUpdateColumn(array &$sets, array &$params, string $column, mixed $value, bool $enabled): void
    {
        if (!$enabled) {
            return;
        }

        $sets[] = '`' . $column . '` = :' . $column;
        $params[$column] = $value;
    }

    /** @param list<string> $columns */
    private function addOptionalSelectColumn(array &$columns, string $column, bool $enabled): void
    {
        if ($enabled) {
            $columns[] = $column;
        }
    }

    private function normaliseNullable(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }

    private function nullableString(mixed $value): ?string
    {
        return $value === null ? null : (string) $value;
    }

    /**
     * Driver-aware column existence check.
     *
     * SQLite has no INFORMATION_SCHEMA; use PRAGMA table_info. MySQL and other
     * servers use the SQL-standard INFORMATION_SCHEMA.COLUMNS.
     */
    private function columnExists(string $table, string $column): bool
    {
        foreach ($this->tableColumns($table) as $existing) {
            if (strcasecmp($existing, $column) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<string>
     */
    private function tableColumns(string $table): array
    {
        if ($this->isSqlite()) {
            $safe = $this->assertIdentifier($table);
            $statement = $this->pdo->query('PRAGMA table_info("' . $safe . '")');
            if ($statement === false) {
                return [];
            }

            $columns = [];
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                if (isset($row['name'])) {
                    $columns[] = (string) $row['name'];
                }
            }

            return $columns;
        }

        $statement = $this->pdo->prepare(
            'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table'
        );
        $statement->execute(['table' => $table]);

        return array_map(static fn (mixed $v): string => (string) $v, $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * Driver-aware table existence check.
     */
    private function tableExists(string $table): bool
    {
        if ($this->isSqlite()) {
            $statement = $this->pdo->prepare(
                "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = :table"
            );
            $statement->execute(['table' => $table]);

            return (int) $statement->fetchColumn() > 0;
        }

        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table'
        );
        $statement->execute(['table' => $table]);

        return (int) $statement->fetchColumn() > 0;
    }

    private function isSqlite(): bool
    {
        return strtolower((string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) === 'sqlite';
    }

    /**
     * Guard a table identifier used in a PRAGMA statement (which cannot bind
     * parameters). Table names in this repository are trusted constants, but we
     * validate defensively.
     */
    private function assertIdentifier(string $identifier): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier) !== 1) {
            throw new \InvalidArgumentException('Unsafe table identifier: ' . $identifier);
        }

        return $identifier;
    }
}
