<?php

declare(strict_types=1);

namespace Zoosper\Media\Lifecycle;

use PDO;
use Zoosper\Media\Model\MediaAsset;

/**
 * Counts current and restorable Page references to a Media public path.
 *
 * Page stores the canonical /media/... URL in generated HTML and Editor.js JSON.
 * Matching the complete, hash-based public path avoids filename-only collisions.
 */
final readonly class MediaReferenceInspector
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return array{pages: int, page_revisions: int} */
    public function counts(MediaAsset $asset): array
    {
        if ($asset->publicPath === null || trim($asset->publicPath) === '') {
            return ['pages' => 0, 'page_revisions' => 0];
        }

        return [
            'pages' => $this->countReferences('pages', $asset->publicPath),
            'page_revisions' => $this->countReferences('page_revisions', $asset->publicPath),
        ];
    }

    private function countReferences(string $table, string $publicPath): int
    {
        if (!$this->tableExists($table)) {
            return 0;
        }
        $columns = $this->columns($table);
        $conditions = [];
        $params = [];
        if (in_array('content', $columns, true)) {
            $conditions[] = 'content LIKE :content_reference';
            $params['content_reference'] = '%' . $publicPath . '%';
        }
        if (in_array('content_json', $columns, true)) {
            $conditions[] = 'content_json LIKE :json_reference';
            $params['json_reference'] = '%' . $publicPath . '%';
        }
        if ($conditions === []) {
            return 0;
        }
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM ' . $table . ' WHERE ' . implode(' OR ', $conditions)
        );
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    /** @return list<string> */
    private function columns(string $table): array
    {
        if ($this->isSqlite()) {
            $statement = $this->pdo->query('PRAGMA table_info(' . $table . ')');
            return array_map(
                static fn (array $row): string => (string) $row['name'],
                $statement === false ? [] : $statement->fetchAll(PDO::FETCH_ASSOC)
            );
        }
        $statement = $this->pdo->prepare(
            'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table'
        );
        $statement->execute(['table' => $table]);
        return array_map(static fn (mixed $value): string => (string) $value, $statement->fetchAll(PDO::FETCH_COLUMN));
    }

    private function tableExists(string $table): bool
    {
        if ($this->isSqlite()) {
            $statement = $this->pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = :table");
            $statement->execute(['table' => $table]);
            return (int) $statement->fetchColumn() > 0;
        }
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table'
        );
        $statement->execute(['table' => $table]);
        return (int) $statement->fetchColumn() > 0;
    }

    private function isSqlite(): bool
    {
        return strtolower((string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) === 'sqlite';
    }
}
