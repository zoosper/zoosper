<?php

declare(strict_types=1);

namespace Zoosper\Admin\PageMomentum;

use PDO;

/**
 * Column-map-driven, read-only implementation of the page momentum query.
 *
 * Unlike a fixed-column query, this adapter first inspects which columns the
 * `pages` table actually has and degrades gracefully: if a needed column is
 * missing, the affected fact reports 0 (or null) instead of throwing a SQL
 * error on the dashboard. It only ever reads; it never writes.
 *
 * Date-window filtering is performed in PHP against a configurable "now" so the
 * behaviour is deterministic and portable across SQLite (tests) and MySQL.
 */
final class SchemaAdaptivePageMomentumQuery implements PageMomentumQueryInterface
{
    /** @var array<string, bool>|null */
    private ?array $availableColumns = null;

    public function __construct(
        private readonly PDO $pdo,
        private readonly PageMomentumColumnMap $columns,
        private readonly ?\DateTimeImmutable $now = null,
    ) {
        $this->columns->assertSafeIdentifiers();
    }

    public function countTotalPages(): int
    {
        return $this->scalarCount('SELECT COUNT(*) FROM ' . $this->columns->table);
    }

    public function countPublishedPages(): int
    {
        if (!$this->hasColumn($this->columns->status)) {
            return 0;
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM ' . $this->columns->table
            . ' WHERE ' . $this->columns->status . ' = :val'
        );
        $stmt->execute([':val' => $this->columns->publishedValue]);

        return (int) $stmt->fetchColumn();
    }

    public function countDraftPages(): int
    {
        if (!$this->hasColumn($this->columns->status)) {
            // With no status column we cannot distinguish drafts; treat all as draft.
            return $this->countTotalPages();
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM ' . $this->columns->table
            . ' WHERE ' . $this->columns->status . ' IS NULL OR '
            . $this->columns->status . ' <> :val'
        );
        $stmt->execute([':val' => $this->columns->publishedValue]);

        return (int) $stmt->fetchColumn();
    }

    public function countPublishedSince(int $days): int
    {
        if (!$this->hasColumn($this->columns->publishedAt)) {
            return 0;
        }

        return $this->countSince(
            $this->columns->publishedAt,
            $days,
            requirePublished: $this->hasColumn($this->columns->status)
        );
    }

    public function countUpdatedSince(int $days): int
    {
        if (!$this->hasColumn($this->columns->updatedAt)) {
            return 0;
        }

        return $this->countSince($this->columns->updatedAt, $days, requirePublished: false);
    }

    /**
     * @return array{title: string, updated_at: string}|null
     */
    public function mostRecentlyUpdatedPage(): ?array
    {
        if (!$this->hasColumn($this->columns->updatedAt) || !$this->hasColumn($this->columns->title)) {
            return null;
        }

        $sql = 'SELECT ' . $this->columns->title . ' AS t, '
            . $this->columns->updatedAt . ' AS u FROM ' . $this->columns->table
            . ' WHERE ' . $this->columns->updatedAt . ' IS NOT NULL'
            . ' ORDER BY ' . $this->columns->updatedAt . ' DESC LIMIT 1';

        $stmt = $this->pdo->query($sql);
        $row = $stmt === false ? false : $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row) || !isset($row['t'], $row['u'])) {
            return null;
        }

        return [
            'title' => (string) $row['t'],
            'updated_at' => (string) $row['u'],
        ];
    }

    private function countSince(string $column, int $days, bool $requirePublished): int
    {
        $now = $this->now ?? new \DateTimeImmutable('now');
        $threshold = $now->sub(new \DateInterval('P' . max(0, $days) . 'D'));

        $sql = 'SELECT ' . $column . ' FROM ' . $this->columns->table
            . ' WHERE ' . $column . ' IS NOT NULL';
        if ($requirePublished && $this->hasColumn($this->columns->status)) {
            $sql .= " AND " . $this->columns->status . " = '" . $this->columns->publishedValue . "'";
        }

        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            return 0;
        }

        $count = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $value) {
            $timestamp = $this->parseTimestamp((string) $value);
            if ($timestamp !== null && $timestamp >= $threshold) {
                $count++;
            }
        }

        return $count;
    }

    private function parseTimestamp(string $value): ?\DateTimeImmutable
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Exception) {
            return null;
        }
    }

    private function scalarCount(string $sql): int
    {
        $stmt = $this->pdo->query($sql);

        return $stmt === false ? 0 : (int) $stmt->fetchColumn();
    }

    /**
     * Determine whether a column exists on the configured table, cached per
     * instance. Uses a bounded SELECT that works across SQLite and MySQL.
     */
    private function hasColumn(string $column): bool
    {
        if ($this->availableColumns === null) {
            $this->availableColumns = $this->discoverColumns();
        }

        return $this->availableColumns[$column] ?? false;
    }

    /**
     * @return array<string, bool>
     */
    private function discoverColumns(): array
    {
        // A zero-row select is the most portable way to read column metadata.
        $stmt = $this->pdo->query('SELECT * FROM ' . $this->columns->table . ' LIMIT 0');
        if ($stmt === false) {
            return [];
        }

        $columns = [];
        $columnCount = $stmt->columnCount();
        for ($i = 0; $i < $columnCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            if (is_array($meta) && isset($meta['name'])) {
                $columns[(string) $meta['name']] = true;
            }
        }

        return $columns;
    }
}
