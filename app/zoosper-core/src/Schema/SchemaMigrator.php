<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

use PDO;

final readonly class SchemaMigrator
{
    public function __construct(private PDO $pdo, private string $driver)
    {
    }

    /** @return list<string> */
    public function diff(SchemaRegistry $registry): array
    {
        $inspector = new SchemaInspector($this->pdo, $this->driver);
        $builder = new SchemaSqlBuilder($this->driver);
        $sql = [];

        foreach ($registry->tables() as $table) {
            if (!$inspector->tableExists($table->name)) {
                $sql[] = $builder->createTableSql($table);
                continue;
            }

            foreach ($table->columns as $column => $definition) {
                if (!$inspector->columnExists($table->name, (string) $column)) {
                    $sql[] = $builder->addColumnSql($table->name, (string) $column, $definition);
                }
            }

            foreach ($table->indexes as $indexName => $definition) {
                if (!$inspector->indexExists($table->name, (string) $indexName)) {
                    $sql[] = $builder->createIndexSql($table->name, (string) $indexName, $definition);
                }
            }
        }

        return $sql;
    }

    /** @return list<string> */
    public function apply(SchemaRegistry $registry): array
    {
        $statements = $this->diff($registry);
        foreach ($statements as $sql) {
            $this->pdo->exec($sql);
        }
        return $statements;
    }
}
