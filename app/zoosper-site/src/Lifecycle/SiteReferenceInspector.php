<?php

declare(strict_types=1);

namespace Zoosper\Site\Lifecycle;

use PDO;

/** Read-only counts that protect Site permanent deletion from database cascades. */
final readonly class SiteReferenceInspector
{
    public function __construct(private PDO $pdo) {}

    /** @return array{domains:int,pages:int,page_assignments:int,menus:int,url_rewrites:int} */
    public function counts(int $siteId): array
    {
        return [
            'domains' => $this->count('site_domains', 'site_id = :id', ['id' => $siteId]),
            'pages' => $this->count('pages', 'site_id = :id', ['id' => $siteId]),
            'page_assignments' => $this->count('page_site_assignments', 'site_id = :id', ['id' => $siteId]),
            'menus' => $this->count('menus', 'site_id = :id', ['id' => $siteId]),
            'url_rewrites' => $this->count('url_rewrites', 'site_id = :id', ['id' => $siteId]),
        ];
    }

    /** @param array<string,int|string> $parameters */
    private function count(string $table, string $where, array $parameters): int
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
        } else {
            $statement = $this->pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=:table');
        }
        $statement->execute(['table' => $table]);
        return (int) $statement->fetchColumn() > 0;
    }
}










