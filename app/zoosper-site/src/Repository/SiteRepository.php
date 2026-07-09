<?php

declare(strict_types=1);

namespace Zoosper\Site\Repository;

use PDO;
use RuntimeException;
use Zoosper\Site\Model\Site;

final readonly class SiteRepository
{
    public function __construct(private PDO $pdo) {}

    public function findActiveByHost(string $host): ?Site
    {
        $statement = $this->pdo->prepare('SELECT s.* FROM sites s INNER JOIN site_domains d ON d.site_id = s.id WHERE d.host = :host AND s.status = :status LIMIT 1');
        $statement->execute(['host' => strtolower($host), 'status' => 'active']);
        $row = $statement->fetch();
        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findByCode(string $code): ?Site
    {
        $statement = $this->pdo->prepare('SELECT * FROM sites WHERE code = :code LIMIT 1');
        $statement->execute(['code' => $code]);
        $row = $statement->fetch();
        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function findById(int $id): ?Site
    {
        $statement = $this->pdo->prepare('SELECT * FROM sites WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        return is_array($row) ? $this->hydrate($row) : null;
    }

    /** @return list<Site> */
    public function allActive(): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM sites WHERE status = :status ORDER BY name ASC');
        $statement->execute(['status' => 'active']);
        return array_map(fn (array $row): Site => $this->hydrate($row), $statement->fetchAll());
    }

    public function create(string $code, string $name, string $host, string $homepageSlug = 'home', string $themeCode = 'default'): int
    {
        if ($this->findByCode($code) !== null) {
            throw new RuntimeException('Site already exists: ' . $code);
        }
        $now = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare('INSERT INTO sites (code, name, status, homepage_slug, theme_code, created_at, updated_at) VALUES (:code, :name, :status, :homepage_slug, :theme_code, :created_at, :updated_at)');
        $statement->execute(['code' => $code, 'name' => $name, 'status' => 'active', 'homepage_slug' => $homepageSlug, 'theme_code' => $themeCode, 'created_at' => $now, 'updated_at' => $now]);
        $siteId = (int) $this->pdo->lastInsertId();
        $this->addDomain($siteId, $host, true);
        return $siteId;
    }

    public function updateTheme(int $siteId, string $themeCode): void
    {
        $statement = $this->pdo->prepare('UPDATE sites SET theme_code = :theme_code, updated_at = :updated_at WHERE id = :id');
        $statement->execute(['id' => $siteId, 'theme_code' => $themeCode, 'updated_at' => gmdate('Y-m-d H:i:s')]);
    }

    public function addDomain(int $siteId, string $host, bool $isPrimary = false): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare('INSERT INTO site_domains (site_id, host, is_primary, created_at, updated_at) VALUES (:site_id, :host, :is_primary, :created_at, :updated_at)');
        $statement->execute(['site_id' => $siteId, 'host' => strtolower($host), 'is_primary' => $isPrimary ? 1 : 0, 'created_at' => $now, 'updated_at' => $now]);
    }

    /** @param array<string, mixed> $row */
    private function hydrate(array $row): Site
    {
        return new Site(
            id: (int) $row['id'],
            code: (string) $row['code'],
            name: (string) $row['name'],
            status: (string) $row['status'],
            homepageSlug: $row['homepage_slug'] !== null ? (string) $row['homepage_slug'] : null,
            themeCode: (string) ($row['theme_code'] ?? 'default'),
        );
    }
}
