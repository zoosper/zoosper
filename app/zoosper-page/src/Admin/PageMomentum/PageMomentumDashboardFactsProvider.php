<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\PageMomentum;

use PDO;
use Throwable;

/**
 * Provides read-only operational facts for the Page Momentum dashboard.
 *
 * Important architectural rule:
 * - This provider must never write to the database.
 * - It must tolerate optional tables because local/dev installs may not have
 *   every future module enabled yet.
 * - It should fail soft and return available facts instead of breaking admin.
 */
final class PageMomentumDashboardFactsProvider
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    /**
     * @return list<PageMomentumDashboardFact>
     */
    public function facts(): array
    {
        return [
            new PageMomentumDashboardFact(
                key: 'total_pages',
                label: 'Total pages',
                value: $this->countRows('pages'),
                description: 'All CMS pages currently known to the page module.',
                status: 'neutral',
            ),
            new PageMomentumDashboardFact(
                key: 'published_pages',
                label: 'Published pages',
                value: $this->countWhere('pages', ['status' => ['published', 'active', 'enabled']]),
                description: 'Pages that appear to be published or active.',
                status: 'good',
            ),
            new PageMomentumDashboardFact(
                key: 'draft_pages',
                label: 'Draft pages',
                value: $this->countWhere('pages', ['status' => ['draft', 'unpublished']]),
                description: 'Pages that still appear to be drafts or unpublished.',
                status: 'attention',
            ),
            new PageMomentumDashboardFact(
                key: 'disabled_pages',
                label: 'Disabled pages',
                value: $this->countWhere('pages', ['status' => ['disabled', 'inactive']]),
                description: 'Pages that appear to be disabled or inactive.',
                status: 'attention',
            ),
            new PageMomentumDashboardFact(
                key: 'missing_seo_title',
                label: 'Missing SEO title',
                value: $this->countBlankColumnIfExists('pages', ['meta_title', 'seo_title']),
                description: 'Pages where a known SEO title column is blank.',
                status: 'warning',
            ),
            new PageMomentumDashboardFact(
                key: 'missing_seo_description',
                label: 'Missing SEO description',
                value: $this->countBlankColumnIfExists('pages', ['meta_description', 'seo_description']),
                description: 'Pages where a known SEO description column is blank.',
                status: 'warning',
            ),
            new PageMomentumDashboardFact(
                key: 'url_rewrites',
                label: 'URL rewrites',
                value: $this->countFirstExistingTable(['url_rewrites', 'url_rewrite']),
                description: 'URL rewrite records available for routing and SEO continuity.',
                status: 'neutral',
            ),
            new PageMomentumDashboardFact(
                key: 'sites',
                label: 'Sites',
                value: $this->countFirstExistingTable(['sites', 'site']),
                description: 'Configured sites available to the CMS.',
                status: 'neutral',
            ),
            new PageMomentumDashboardFact(
                key: 'domains',
                label: 'Domains',
                value: $this->countFirstExistingTable(['domains', 'site_domains', 'site_domain']),
                description: 'Configured domains mapped into the CMS.',
                status: 'neutral',
            ),
        ];
    }

    /**
     * @return list<array{key:string,label:string,value:int,description:string,status:string}>
     */
    public function factsAsArray(): array
    {
        return array_map(
            static fn (PageMomentumDashboardFact $fact): array => $fact->toArray(),
            $this->facts(),
        );
    }

    private function countRows(string $table): int
    {
        if (!$this->tableExists($table)) {
            return 0;
        }

        try {
            return (int) $this->pdo->query(sprintf('SELECT COUNT(*) FROM %s', $this->quoteIdentifier($table)))->fetchColumn();
        } catch (Throwable) {
            return 0;
        }
    }

    /**
     * @param array<string,list<string>> $conditions
     */
    private function countWhere(string $table, array $conditions): int
    {
        if (!$this->tableExists($table)) {
            return 0;
        }

        foreach ($conditions as $column => $values) {
            if (!$this->columnExists($table, $column)) {
                continue;
            }

            try {
                $placeholders = implode(',', array_fill(0, count($values), '?'));
                $sql = sprintf(
                    'SELECT COUNT(*) FROM %s WHERE LOWER(%s) IN (%s)',
                    $this->quoteIdentifier($table),
                    $this->quoteIdentifier($column),
                    $placeholders,
                );

                $statement = $this->pdo->prepare($sql);
                $statement->execute(array_map(static fn (string $value): string => strtolower($value), $values));

                return (int) $statement->fetchColumn();
            } catch (Throwable) {
                return 0;
            }
        }

        return 0;
    }

    /**
     * @param list<string> $columns
     */
    private function countBlankColumnIfExists(string $table, array $columns): int
    {
        if (!$this->tableExists($table)) {
            return 0;
        }

        foreach ($columns as $column) {
            if (!$this->columnExists($table, $column)) {
                continue;
            }

            try {
                $sql = sprintf(
                    'SELECT COUNT(*) FROM %s WHERE %s IS NULL OR TRIM(%s) = \'\'',
                    $this->quoteIdentifier($table),
                    $this->quoteIdentifier($column),
                    $this->quoteIdentifier($column),
                );

                return (int) $this->pdo->query($sql)->fetchColumn();
            } catch (Throwable) {
                return 0;
            }
        }

        return 0;
    }

    /**
     * @param list<string> $tables
     */
    private function countFirstExistingTable(array $tables): int
    {
        foreach ($tables as $table) {
            if ($this->tableExists($table)) {
                return $this->countRows($table);
            }
        }

        return 0;
    }

    private function tableExists(string $table): bool
    {
        try {
            $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

            if ($driver === 'sqlite') {
                $statement = $this->pdo->prepare("SELECT name FROM sqlite_master WHERE type = 'table' AND name = ? LIMIT 1");
                $statement->execute([$table]);

                return (bool) $statement->fetchColumn();
            }

            $statement = $this->pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            );
            $statement->execute([$table]);

            return (int) $statement->fetchColumn() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

            if ($driver === 'sqlite') {
                $statement = $this->pdo->query(sprintf('PRAGMA table_info(%s)', $this->quoteIdentifier($table)));

                foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    if (($row['name'] ?? null) === $column) {
                        return true;
                    }
                }

                return false;
            }

            $statement = $this->pdo->prepare(
                'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            );
            $statement->execute([$table, $column]);

            return (int) $statement->fetchColumn() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    private function quoteIdentifier(string $identifier): string
    {
        $driver = '';
        try {
            $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        } catch (Throwable) {
            // Fall through to MySQL-style quoting.
        }

        if ($driver === 'sqlite') {
            return '"' . str_replace('"', '""', $identifier) . '"';
        }

        return '`' . str_replace('`', '``', $identifier) . '`';
    }
}
