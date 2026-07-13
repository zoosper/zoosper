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
 */
final readonly class EntityExtensionValueRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Creates the extension value table where migration runners are not yet wired.
     *
     * Production installs should eventually run this SQL through the declarative
     * schema/migration engine. The method is idempotent for local verification.
     */
    public function ensureSchema(): void
    {
        $this->pdo->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS entity_extension_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(100) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    module VARCHAR(120) NOT NULL,
    field_name VARCHAR(120) NOT NULL,
    value_json JSON NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    UNIQUE KEY uq_entity_extension_field (entity_type, entity_id, module, field_name),
    KEY idx_entity_extension_lookup (entity_type, entity_id),
    KEY idx_entity_extension_module (module)
)
SQL);
    }

    public function upsert(EntityExtensionValue $value): void
    {
        $now = gmdate('Y-m-d H:i:s');
        $statement = $this->pdo->prepare(<<<'SQL'
INSERT INTO entity_extension_values
    (entity_type, entity_id, module, field_name, value_json, created_at, updated_at)
VALUES
    (:entity_type, :entity_id, :module, :field_name, :value_json, :created_at, :updated_at)
ON DUPLICATE KEY UPDATE
    value_json = VALUES(value_json),
    updated_at = VALUES(updated_at)
SQL);

        $statement->execute([
            'entity_type' => $value->entityType,
            'entity_id' => (int) $value->entityId,
            'module' => $value->module,
            'field_name' => $value->fieldName,
            'value_json' => json_encode($value->value, JSON_THROW_ON_ERROR),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
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
