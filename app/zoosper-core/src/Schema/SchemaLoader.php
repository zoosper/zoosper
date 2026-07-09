<?php

declare(strict_types=1);

namespace Zoosper\Core\Schema;

use RuntimeException;
use Zoosper\Core\Module\ModuleRegistry;

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
                throw new RuntimeException('Declarative schema file must return an array: ' . $file);
            }

            foreach ($config as $tableName => $table) {
                if (!is_array($table)) {
                    throw new RuntimeException('Invalid table declaration for ' . $tableName . ' in ' . $file);
                }
                $registry->addTable(new SchemaTable(
                    name: (string) $tableName,
                    columns: $table['columns'] ?? [],
                    indexes: $table['indexes'] ?? [],
                ));
            }
        }

        return $registry;
    }
}
