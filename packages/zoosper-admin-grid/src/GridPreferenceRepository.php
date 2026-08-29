<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use JsonException;
use PDO;

final readonly class GridPreferenceRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array{visible_columns: list<string>, column_order: list<string>}|null
     */
    public function findColumnPreferences(int $adminUserId, string $gridKey): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT visible_columns_json FROM admin_grid_preferences '
            . 'WHERE admin_user_id = :uid AND grid_key = :grid LIMIT 1',
        );
        $statement->execute(['uid' => $adminUserId, 'grid' => $gridKey]);
        $json = $statement->fetchColumn();
        if (!is_string($json) || $json === '') {
            return null;
        }

        try {
            $decoded = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }
        if (!is_array($decoded)) {
            return null;
        }

        // Rows written before column-order persistence stored a plain list.
        if (array_is_list($decoded)) {
            return [
                'visible_columns' => $this->stringList($decoded),
                'column_order' => [],
            ];
        }

        if (
            !is_array($decoded['visible_columns'] ?? null)
            || !is_array($decoded['column_order'] ?? null)
        ) {
            return null;
        }

        return [
            'visible_columns' => $this->stringList($decoded['visible_columns']),
            'column_order' => $this->stringList($decoded['column_order']),
        ];
    }

    /** @return list<string>|null */
    public function findVisibleColumns(int $adminUserId, string $gridKey): ?array
    {
        return $this->findColumnPreferences($adminUserId, $gridKey)['visible_columns'] ?? null;
    }

    /** @return list<string>|null */
    public function findColumnOrder(int $adminUserId, string $gridKey): ?array
    {
        $preferences = $this->findColumnPreferences($adminUserId, $gridKey);

        return $preferences !== null && $preferences['column_order'] !== []
            ? $preferences['column_order']
            : null;
    }

    /**
     * @param list<string> $visibleColumnKeys
     * @param list<string> $columnOrder
     */
    public function saveColumnPreferences(
        int $adminUserId,
        string $gridKey,
        array $visibleColumnKeys,
        array $columnOrder,
    ): void {
        $json = json_encode([
            'visible_columns' => array_values($visibleColumnKeys),
            'column_order' => array_values($columnOrder),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $now = gmdate('Y-m-d H:i:s');
        $existing = $this->pdo->prepare(
            'SELECT id FROM admin_grid_preferences '
            . 'WHERE admin_user_id = :uid AND grid_key = :grid LIMIT 1',
        );
        $existing->execute(['uid' => $adminUserId, 'grid' => $gridKey]);
        $id = $existing->fetchColumn();

        if ($id !== false) {
            $update = $this->pdo->prepare(
                'UPDATE admin_grid_preferences '
                . 'SET visible_columns_json = :json, updated_at = :updated_at WHERE id = :id',
            );
            $update->execute(['json' => $json, 'updated_at' => $now, 'id' => (int) $id]);
            return;
        }

        $insert = $this->pdo->prepare(
            'INSERT INTO admin_grid_preferences '
            . '(admin_user_id, grid_key, visible_columns_json, updated_at) '
            . 'VALUES (:uid, :grid, :json, :updated_at)',
        );
        $insert->execute([
            'uid' => $adminUserId,
            'grid' => $gridKey,
            'json' => $json,
            'updated_at' => $now,
        ]);
    }

    /**
     * Backwards-compatible visibility-only writer for existing integrations.
     *
     * @param list<string> $visibleColumnKeys
     */
    public function saveVisibleColumns(
        int $adminUserId,
        string $gridKey,
        array $visibleColumnKeys,
    ): void {
        $this->saveColumnPreferences(
            $adminUserId,
            $gridKey,
            $visibleColumnKeys,
            $this->findColumnOrder($adminUserId, $gridKey) ?? [],
        );
    }

    public function clear(int $adminUserId, string $gridKey): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM admin_grid_preferences WHERE admin_user_id = :uid AND grid_key = :grid',
        );
        $statement->execute(['uid' => $adminUserId, 'grid' => $gridKey]);
    }

    /** @param array<mixed> $values @return list<string> */
    private function stringList(array $values): array
    {
        $result = [];
        foreach ($values as $value) {
            if (!is_string($value)) {
                continue;
            }
            $value = trim($value);
            if ($value !== '' && !in_array($value, $result, true)) {
                $result[] = $value;
            }
        }

        return $result;
    }
}
