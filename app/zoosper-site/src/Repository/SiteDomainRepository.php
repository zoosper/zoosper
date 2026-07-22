<?php

declare(strict_types=1);

namespace Zoosper\Site\Repository;

use PDO;
use Zoosper\Site\Model\SiteDomain;

/** Repository for admin-managed site domain mappings. */
final readonly class SiteDomainRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<SiteDomain> */
    public function all(): array
    {
        $statement = $this->pdo->query('SELECT * FROM site_domains ORDER BY host ASC');

        return array_map(fn (array $row): SiteDomain => $this->hydrate($row), $statement->fetchAll());
    }

    public function findById(int $id): ?SiteDomain
    {
        $statement = $this->pdo->prepare('SELECT * FROM site_domains WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function create(int $siteId, string $host, bool $isPrimary = false): int
    {
        $host = strtolower($host);
        $this->clearPrimaryIfNeeded($siteId, $isPrimary);
        $now = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(
            'INSERT INTO site_domains (site_id, host, is_primary, created_at, updated_at) '
            . 'VALUES (:site_id, :host, :is_primary, :created_at, :updated_at)'
        );
        $statement->execute([
            'site_id' => $siteId,
            'host' => $host,
            'is_primary' => $isPrimary ? 1 : 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, int $siteId, string $host, bool $isPrimary = false): void
    {
        $this->clearPrimaryIfNeeded($siteId, $isPrimary, $id);
        $statement = $this->pdo->prepare('UPDATE site_domains SET site_id = :site_id, host = :host, is_primary = :is_primary, updated_at = :updated_at WHERE id = :id');
        $statement->execute([
            'id' => $id,
            'site_id' => $siteId,
            'host' => strtolower($host),
            'is_primary' => $isPrimary ? 1 : 0,
            'updated_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    private function clearPrimaryIfNeeded(int $siteId, bool $isPrimary, ?int $excludingId = null): void
    {
        if (!$isPrimary) {
            return;
        }

        $sql = 'UPDATE site_domains SET is_primary = 0, updated_at = :updated_at WHERE site_id = :site_id';
        $params = ['site_id' => $siteId, 'updated_at' => gmdate('Y-m-d H:i:s')];
        if ($excludingId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $excludingId;
        }

        $this->pdo->prepare($sql)->execute($params);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): SiteDomain
    {
        return new SiteDomain(
            id: (int) $row['id'],
            siteId: (int) $row['site_id'],
            host: (string) $row['host'],
            isPrimary: (bool) $row['is_primary'],
        );
    }
}
