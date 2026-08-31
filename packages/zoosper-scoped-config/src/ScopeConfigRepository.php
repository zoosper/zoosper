<?php

declare(strict_types=1);

namespace Zoosper\ScopedConfig;

use PDO;

/**
 * DB-backed, admin-editable configuration with website/store/site scope
 * fallback resolution — the "Part 3" scope-config engine from the CMS
 * architecture design discussion. This is DELIBERATELY SEPARATE from
 * Zoosper\Core\Config\ConfigRepository (which aggregates STATIC config/*.php
 * files once at boot): this repository is for values an admin changes at
 * runtime through /admin/settings (Phase D3, not yet built), scoped to a
 * specific website, store, or individual site.
 *
 * Resolution order for get(), most specific to least:
 *   1. site    (scope_key = the Site's own id)
 *   2. store   (scope_key = the Site's storeCode)
 *   3. website (scope_key = the Site's websiteCode)
 *   4. default (scope_key = null)
 * The first scope level with a saved row for the given config_path wins.
 */
final readonly class ScopeConfigRepository
{
    public function __construct(
        private PDO $pdo,
        private string $table = 'config_scope_values',
    ) {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $this->table) !== 1) {
            throw new \InvalidArgumentException('Invalid config scope table name.');
        }
    }

    /**
     * Resolve a config value for the given path, trying each scope level from
     * most to least specific, falling back to $default when no row exists at
     * ANY level (including 'default' itself never having been set).
     */
    public function get(string $path, ScopeContext $context, ?string $default = null): ?string
    {
        foreach ($this->resolutionOrder($context) as [$scopeType, $scopeKey]) {
            $value = $this->find($path, $scopeType, $scopeKey);
            if ($value !== null) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Resolve a config value AND report which scope level it came from —
     * useful for an admin UI that wants to show "inherited from Website" vs
     * "overridden at this Site" (Phase D3 concern; exposed here now so that
     * UI does not need to re-implement the resolution order itself).
     *
     * @return array{value: ?string, resolvedScope: ?ScopeType}
     */
    public function getWithSource(string $path, ScopeContext $context, ?string $default = null): array
    {
        foreach ($this->resolutionOrder($context) as [$scopeType, $scopeKey]) {
            $value = $this->find($path, $scopeType, $scopeKey);
            if ($value !== null) {
                return ['value' => $value, 'resolvedScope' => $scopeType];
            }
        }

        return ['value' => $default, 'resolvedScope' => null];
    }

    /**
     * Save (insert or replace) a value at a SPECIFIC scope level. Passing
     * ScopeType::Default requires $scopeKey to be null; every other scope
     * type requires a non-empty $scopeKey.
     */
    public function set(string $path, ScopeType $scopeType, ?string $scopeKey, string $value): void
    {
        $this->assertValidScopeKey($scopeType, $scopeKey);

        $now = gmdate('Y-m-d H:i:s');
        $existingId = $this->findRowId($path, $scopeType, $scopeKey);

        if ($existingId !== null) {
            $statement = $this->pdo->prepare(
                'UPDATE ' . $this->table . ' SET config_value = :value, updated_at = :updated_at WHERE id = :id'
            );
            $statement->execute(['value' => $value, 'updated_at' => $now, 'id' => $existingId]);

            return;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO ' . $this->table . ' (scope_type, scope_key, config_path, config_value, updated_at)'
            . ' VALUES (:scope_type, :scope_key, :config_path, :config_value, :updated_at)'
        );
        $statement->execute([
            'scope_type' => $scopeType->value,
            'scope_key' => $scopeKey,
            'config_path' => $path,
            'config_value' => $value,
            'updated_at' => $now,
        ]);
    }

    /**
     * Remove an override at a specific scope level, so resolution falls back
     * to the next-less-specific level (e.g. "reset to Website default").
     */
    public function clear(string $path, ScopeType $scopeType, ?string $scopeKey): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM ' . $this->table . ' WHERE config_path = :path AND scope_type = :scope_type'
            . ($scopeKey === null ? ' AND scope_key IS NULL' : ' AND scope_key = :scope_key')
        );
        $params = ['path' => $path, 'scope_type' => $scopeType->value];
        if ($scopeKey !== null) {
            $params['scope_key'] = $scopeKey;
        }
        $statement->execute($params);
    }

    /**
     * All saved rows for a given config_path, across every scope — useful for
     * an admin UI that wants to show every level a value is overridden at.
     *
     * @return list<array{scopeType: ScopeType, scopeKey: ?string, value: ?string, updatedAt: string}>
     */
    public function allForPath(string $path): array
    {
        $statement = $this->pdo->prepare(
            'SELECT scope_type, scope_key, config_value, updated_at FROM ' . $this->table
            . ' WHERE config_path = :path ORDER BY scope_type, scope_key'
        );
        $statement->execute(['path' => $path]);

        $rows = [];
        foreach ($statement->fetchAll() as $row) {
            $rows[] = [
                'scopeType' => ScopeType::from((string) $row['scope_type']),
                'scopeKey' => $row['scope_key'] !== null ? (string) $row['scope_key'] : null,
                'value' => $row['config_value'] !== null ? (string) $row['config_value'] : null,
                'updatedAt' => (string) $row['updated_at'],
            ];
        }

        return $rows;
    }

    /**
     * Build the [scopeType, scopeKey] pairs to try, most specific first,
     * based on which identifiers the given context actually has. A context
     * missing a given identifier (e.g. no siteId) simply skips that level.
     *
     * @return list<array{0: ScopeType, 1: ?string}>
     */
    private function resolutionOrder(ScopeContext $context): array
    {
        $order = [];

        if ($context->siteId !== null) {
            $order[] = [ScopeType::Site, (string) $context->siteId];
        }
        if ($context->storeCode !== null) {
            $order[] = [ScopeType::Store, $context->storeCode];
        }
        if ($context->websiteCode !== null) {
            $order[] = [ScopeType::Website, $context->websiteCode];
        }
        $order[] = [ScopeType::Default, null];

        return $order;
    }

    private function find(string $path, ScopeType $scopeType, ?string $scopeKey): ?string
    {
        try {
            $sql = 'SELECT config_value FROM ' . $this->table
                . ' WHERE config_path = :path AND scope_type = :scope_type'
                . ($scopeKey === null ? ' AND scope_key IS NULL' : ' AND scope_key = :scope_key')
                . ' LIMIT 1';

            $statement = $this->pdo->prepare($sql);
            $params = ['path' => $path, 'scope_type' => $scopeType->value];
            if ($scopeKey !== null) {
                $params['scope_key'] = $scopeKey;
            }
            $statement->execute($params);

            $value = $statement->fetchColumn();

            return $value === false ? null : (string) $value;
        } catch (\Throwable) {
            return null;
        }
    }

    private function findRowId(string $path, ScopeType $scopeType, ?string $scopeKey): ?int
    {
        try {
            $sql = 'SELECT id FROM ' . $this->table
                . ' WHERE config_path = :path AND scope_type = :scope_type'
                . ($scopeKey === null ? ' AND scope_key IS NULL' : ' AND scope_key = :scope_key')
                . ' LIMIT 1';

            $statement = $this->pdo->prepare($sql);
            $params = ['path' => $path, 'scope_type' => $scopeType->value];
            if ($scopeKey !== null) {
                $params['scope_key'] = $scopeKey;
            }
            $statement->execute($params);

            $id = $statement->fetchColumn();

            return $id === false ? null : (int) $id;
        } catch (\Throwable) {
            return null;
        }
    }

    private function assertValidScopeKey(ScopeType $scopeType, ?string $scopeKey): void
    {
        if ($scopeType === ScopeType::Default && $scopeKey !== null) {
            throw new \InvalidArgumentException('ScopeType::Default must have a null scope key.');
        }
        if ($scopeType !== ScopeType::Default && ($scopeKey === null || $scopeKey === '')) {
            throw new \InvalidArgumentException($scopeType->value . ' scope requires a non-empty scope key.');
        }
    }
}
