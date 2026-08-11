<?php

declare(strict_types=1);

namespace Zoosper\Page\Lifecycle;

use PDO;

/** Read-only Page reference counts used before irreversible deletion. */
final readonly class PageReferenceInspector
{
    public function __construct(private PDO $pdo) {}

    /** @return array{menu_items:int,url_rewrites:int} */
    public function counts(int $pageId): array
    {
        return [
            'menu_items' => $this->countIfTableExists('menu_items', 'page_id = :id', ['id' => $pageId]),
            'url_rewrites' => $this->countIfTableExists(
                'url_rewrites',
                'entity_type = :type AND entity_id = :id',
                ['type' => 'page', 'id' => $pageId],
            ),
        ];
    }

    /** @param array<string,int|string> $parameters */
    private function countIfTableExists(string $table, string $where, array $parameters): int
    {
        if (!$this->tableExists($table)) { return 0; }
        $statement = $this->pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$where}");
        $statement->execute($parameters);
        return (int) $statement->fetchColumn();
    }

    private function tableExists(string $table): bool
    {
        if ((string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $statement = $this->pdo->prepare("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name=:table");
            $statement->execute(['table' => $table]);
            return (int) $statement->fetchColumn() > 0;
        }
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table');
        $statement->execute(['table' => $table]);
        return (int) $statement->fetchColumn() > 0;
    }
}
