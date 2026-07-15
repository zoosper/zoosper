<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

use Zoosper\Core\Exception\ZoosperException;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Loads module-owned declarative schema into a SchemaRegistry.
 *
 * Phase 1.29: modules declare their tables under a single, unified top-level
 * `['tables' => ['table_name' => ['columns' => [...], 'indexes' => [...]]]]`
 * format. The legacy flat format (table names at the top level) is rejected with
 * a descriptive ZoosperException that explains exactly how to migrate.
 *
 * The pure `tablesFromConfig()` helper turns one config array into SchemaTable
 * objects and is unit-testable without a ModuleRegistry.
 */
final readonly class SchemaLoader
{
    public function __construct(private ModuleRegistry $modules)
    {
    }

    public function load(): SchemaRegistry
    {
        $registry = new SchemaRegistry();

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('db_schema.php');
            if (!is_file($file)) {
                continue;
            }

            $config = require $file;
            if (!is_array($config)) {
                throw new ZoosperException(
                    message: 'Declarative schema file must return an array: ' . $file,
                    context: 'Module `' . $module->name . '` config/db_schema.php did not return an array.',
                    suggestion: "Return an array shaped like ['tables' => ['my_table' => ['columns' => [...], 'indexes' => [...]]]].",
                    docsUrl: 'docs/architecture/schema-engine.md',
                    details: ['module' => $module->name, 'file' => $file, 'returned_type' => get_debug_type($config)],
                );
            }

            foreach ($this->tablesFromConfig($config, $file, $module->name) as $table) {
                $registry->addTable($table);
            }
        }

        return $registry;
    }

    /**
     * Turn a single db_schema.php config array into SchemaTable objects.
     *
     * @param array<string, mixed> $config
     * @return list<SchemaTable>
     */
    public function tablesFromConfig(array $config, string $file = '(inline)', string $module = '(inline)'): array
    {
        if ($config === []) {
            return [];
        }

        if (!array_key_exists('tables', $config)) {
            throw new ZoosperException(
                message: 'Declarative schema must use the top-level "tables" key: ' . $file,
                context: 'Module `' . $module . '` uses the legacy flat schema format (table names at the top level). Zoosper now uses a single unified format with a top-level "tables" key.',
                suggestion: "Wrap your tables: change `return ['my_table' => [...]];` to `return ['tables' => ['my_table' => [...]]];`.",
                docsUrl: 'docs/architecture/schema-engine.md',
                details: ['module' => $module, 'file' => $file, 'top_level_keys' => array_keys($config)],
            );
        }

        $tables = $config['tables'];
        if (!is_array($tables)) {
            throw new ZoosperException(
                message: 'The "tables" key must be an array: ' . $file,
                context: 'The top-level "tables" entry must map table names to definitions.',
                suggestion: "Use `['tables' => ['my_table' => ['columns' => [...]]]]`.",
                docsUrl: 'docs/architecture/schema-engine.md',
                details: ['module' => $module, 'file' => $file, 'tables_type' => get_debug_type($tables)],
            );
        }

        $result = [];
        foreach ($tables as $tableName => $table) {
            if (!is_array($table)) {
                throw new ZoosperException(
                    message: 'Invalid table declaration for ' . $tableName . ' in ' . $file,
                    context: 'Each table under "tables" must be an array with columns (and optional indexes).',
                    suggestion: "Define the table as ['columns' => [...], 'indexes' => [...]].",
                    docsUrl: 'docs/architecture/schema-engine.md',
                    details: ['module' => $module, 'file' => $file, 'table' => (string) $tableName, 'table_type' => get_debug_type($table)],
                );
            }

            $result[] = new SchemaTable(
                name: (string) $tableName,
                columns: $table['columns'] ?? [],
                indexes: $table['indexes'] ?? [],
            );
        }

        return $result;
    }
}
