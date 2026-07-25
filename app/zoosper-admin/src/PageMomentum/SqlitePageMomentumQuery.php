<?php

declare(strict_types=1);

namespace Zoosper\Admin\PageMomentum;

use PDO;

/**
 * PDO-backed read-only implementation of the page momentum query contract.
 *
 * Uses portable SELECT statements that work on both SQLite (tests) and MySQL
 * (production). It reads only; it never writes. Date filtering is performed in
 * PHP against a configurable "now" so the logic is deterministic and testable
 * across database engines that differ in date function support.
 *
 * Assumed minimal schema on the `pages` table:
 *   - status     TEXT/VARCHAR  ('published' or otherwise treated as draft)
 *   - title      TEXT/VARCHAR
 *   - published_at  nullable datetime string
 *   - updated_at    nullable datetime string
 */
final class SqlitePageMomentumQuery implements PageMomentumQueryInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $table = 'pages',
        private readonly ?\DateTimeImmutable $now = null,
    ) {
    }

    public function countTotalPages(): int
    {
        return $this->scalarCount("SELECT COUNT(*) FROM {$this->quotedTable()}");
    }

    public function countPublishedPages(): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->quotedTable()} WHERE status = :status"
        );
        $stmt->execute([':status' => 'published']);

        return (int) $stmt->fetchColumn();
    }

    public function countDraftPages(): int
    {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) FROM {$this->quotedTable()} WHERE status IS NULL OR status <> :status"
        );
        $stmt->execute([':status' => 'published']);

        return (int) $stmt->fetchColumn();
    }

    public function countPublishedSince(int $days): int
    {
        return $this->countSince('published_at', $days, requirePublished: true);
    }

    public function countUpdatedSince(int $days): int
    {
        return $this->countSince('updated_at', $days, requirePublished: false);
    }

    /**
     * @return array{title: string, updated_at: string}|null
     */
    public function mostRecentlyUpdatedPage(): ?array
    {
        $stmt = $this->pdo->query(
            "SELECT title, updated_at FROM {$this->quotedTable()} "
            . 'WHERE updated_at IS NOT NULL ORDER BY updated_at DESC LIMIT 1'
        );

        $row = $stmt === false ? false : $stmt->fetch(PDO::FETCH_ASSOC);
        if (!is_array($row) || !isset($row['title'], $row['updated_at'])) {
            return null;
        }

        return [
            'title' => (string) $row['title'],
            'updated_at' => (string) $row['updated_at'],
        ];
    }

    /**
     * Count rows whose $column timestamp is within the last $days days.
     */
    private function countSince(string $column, int $days, bool $requirePublished): int
    {
        $now = $this->now ?? new \DateTimeImmutable('now');
        $threshold = $now->sub(new \DateInterval('P' . max(0, $days) . 'D'));

        $sql = "SELECT {$column} FROM {$this->quotedTable()} WHERE {$column} IS NOT NULL";
        if ($requirePublished) {
            $sql .= " AND status = 'published'";
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

    private function quotedTable(): string
    {
        // Restrict to a safe identifier to avoid injection via the table name.
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $this->table) !== 1) {
            throw new \InvalidArgumentException('Invalid table name for page momentum query.');
        }

        return $this->table;
    }
}
