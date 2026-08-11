<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

use PDO;
use RuntimeException;

/** Driver-aware, read-only live foreign-key inspection. */
final readonly class SchemaForeignKeyInspector
{
    public function __construct(private PDO $pdo, private string $driver)
    {
    }

    /** @return array<string, SchemaForeignKeyState> */
    public function forTable(string $table): array
    {
        $table = $this->identifier($table);

        return $this->driver === 'sqlite'
            ? $this->sqlite($table)
            : $this->mysql($table);
    }

    /** @return array<string, SchemaForeignKeyState> */
    private function sqlite(string $table): array
    {
        $statement = $this->pdo->query('PRAGMA foreign_key_list("' . $table . '")');
        if ($statement === false) {
            return [];
        }

        /** @var array<int, array{table:string,on_update:string,on_delete:string,columns:array<int,string>,referenced:array<int,string>}> $groups */
        $groups = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $id = (int) ($row['id'] ?? 0);
            $seq = (int) ($row['seq'] ?? 0);
            $groups[$id] ??= [
                'table' => (string) ($row['table'] ?? ''),
                'on_update' => (string) ($row['on_update'] ?? 'NO ACTION'),
                'on_delete' => (string) ($row['on_delete'] ?? 'NO ACTION'),
                'columns' => [],
                'referenced' => [],
            ];
            $groups[$id]['columns'][$seq] = (string) ($row['from'] ?? '');
            $groups[$id]['referenced'][$seq] = (string) ($row['to'] ?? '');
        }

        $states = [];
        foreach ($groups as $id => $group) {
            ksort($group['columns']);
            ksort($group['referenced']);
            $name = 'sqlite_fk_' . $table . '_' . $id;
            $states[$name] = new SchemaForeignKeyState(
                $name,
                array_values($group['columns']),
                $group['table'],
                array_values($group['referenced']),
                strtoupper($group['on_delete']),
                strtoupper($group['on_update']),
            );
        }

        return $states;
    }

    /** @return array<string, SchemaForeignKeyState> */
    private function mysql(string $table): array
    {
        $sql = <<<'SQL'
SELECT kcu.CONSTRAINT_NAME, kcu.COLUMN_NAME, kcu.REFERENCED_TABLE_NAME,
       kcu.REFERENCED_COLUMN_NAME, kcu.ORDINAL_POSITION,
       rc.DELETE_RULE, rc.UPDATE_RULE
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc
  ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
 AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
 AND rc.TABLE_NAME = kcu.TABLE_NAME
WHERE kcu.CONSTRAINT_SCHEMA = DATABASE()
  AND kcu.TABLE_NAME = :table
  AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY kcu.CONSTRAINT_NAME, kcu.ORDINAL_POSITION
SQL;
        $statement = $this->pdo->prepare($sql);
        $statement->execute(['table' => $table]);
        $groups = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $name = (string) $row['CONSTRAINT_NAME'];
            $groups[$name] ??= [
                'table' => (string) $row['REFERENCED_TABLE_NAME'],
                'delete' => (string) $row['DELETE_RULE'],
                'update' => (string) $row['UPDATE_RULE'],
                'columns' => [],
                'referenced' => [],
            ];
            $groups[$name]['columns'][] = (string) $row['COLUMN_NAME'];
            $groups[$name]['referenced'][] = (string) $row['REFERENCED_COLUMN_NAME'];
        }

        $states = [];
        foreach ($groups as $name => $group) {
            $states[$name] = new SchemaForeignKeyState(
                $name,
                $group['columns'],
                $group['table'],
                $group['referenced'],
                strtoupper($group['delete']),
                strtoupper($group['update']),
            );
        }

        return $states;
    }

    private function identifier(string $identifier): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier) !== 1) {
            throw new RuntimeException('Unsafe schema identifier: ' . $identifier);
        }

        return $identifier;
    }
}
