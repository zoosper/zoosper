<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Extension;

use PDO;

/**
 * Persists generic extension values for entities.
 *
 * This repository intentionally stores only module-owned extension fields. Core
 * entity columns must continue to be written through their own repositories and
 * field-definition write maps.
 *
 * Phase 1.29 follow-up: table creation is owned by the unified declarative schema
 * engine (see zoosper-core/config/db_schema.php); this repository no longer
 * creates its own table. upsert() is driver-portable across MySQL and SQLite.
 *
 * PCI-aware: value_json stores module extension-field values only, never OTPs,
 * TOTP secrets, recovery codes, reset tokens, payment data or other secrets.
 */
final readonly class EntityExtensionValueRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Insert or update a single module-owned extension value.
     *
     * Uses the correct upsert syntax for the active driver: MySQL/MariaDB
     * `ON DUPLICATE KEY UPDATE` and SQLite `ON CONFLICT ... DO UPDATE`. Both rely
     * on the unique (entity_type, entity_id, module, field_name) constraint.
     */
    public function upsert(EntityExtensionValue $value): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $driver = (string) $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        $params = [
            'entity_type' => $value->entityType,
            'entity_id' => (int) $value->entityId,
            'module' => $value->module,
            'field_name' => $value->fieldName,
            'value_json' => json_encode($value->value, JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if ($driver === 'sqlite') {
            $sql = <<<'SQL'
INSERT INTO entity_extension_values
    (entity_type, entity_id, module, field_name, value_json, created_at, updated_at)
VALUES
    (:entity_type, :entity_id, :module, :field_name, :value_json, :created_at, :updated_at)
ON CONFLICT(entity_type, entity_id, module, field_name) DO UPDATE SET
    value_json = excluded.value_json,
    updated_at = excluded.updated_at
SQL;
        } else {
            $sql = <<<'SQL'
INSERT INTO entity_extension_values
    (entity_type, entity_id, module, field_name, value_json, created_at, updated_at)
VALUES
    (:entity_type, :entity_id, :module, :field_name, :value_json, :created_at, :updated_at)
ON DUPLICATE KEY UPDATE
    value_json = VALUES(value_json),
    updated_at = VALUES(updated_at)
SQL;
        }

        $this->pdo->prepare($sql)->execute($params);
    }

    /**
     * Return all stored extension values for one entity within one module.
     *
     * @return array<string, mixed> field name => decoded value
     */
    public function findForModule(string $entityType, int|string $entityId, string $module): array
    {
        $statement = $this->pdo->prepare(<<<'SQL'
SELECT field_name, value_json
FROM entity_extension_values
WHERE entity_type = :entity_type
  AND entity_id = :entity_id
  AND module = :module
ORDER BY field_name ASC
SQL);
        $statement->execute([
            'entity_type' => $entityType,
            'entity_id' => (int) $entityId,
            'module' => $module,
        ]);

        $values = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $json = is_string($row['value_json'] ?? null) ? $row['value_json'] : 'null';
            $values[(string) $row['field_name']] = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        }

        return $values;
    }
}
