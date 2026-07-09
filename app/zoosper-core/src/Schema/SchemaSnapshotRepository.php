<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

use PDO;

final readonly class SchemaSnapshotRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @param list<string> $statements */
    public function record(array $statements): void
    {
        $hash = hash('sha256', implode("\n", $statements));
        $statement = $this->pdo->prepare(
            'INSERT INTO schema_snapshots (schema_hash, statements_json, created_at)
             VALUES (:schema_hash, :statements_json, :created_at)'
        );
        $statement->execute([
            'schema_hash' => $hash,
            'statements_json' => json_encode($statements, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES),
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<array<string, mixed>> */
    public function latest(int $limit = 20): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM schema_snapshots ORDER BY id DESC LIMIT :limit');
        $statement->bindValue('limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll();
    }
}
