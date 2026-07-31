<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

use JsonException;
use PDO;

final readonly class GridBookmarkRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return list<array{id: int, name: string, state: array<string, mixed>, is_default: bool}> */
    public function allForUser(int $adminUserId, string $gridKey): array
    {
        $statement = $this->pdo->prepare(
            'SELECT id, name, state_json, is_default '
            . 'FROM admin_grid_bookmarks '
            . 'WHERE admin_user_id = :user_id AND grid_key = :grid_key '
            . 'ORDER BY name ASC, id ASC',
        );
        $statement->execute(['user_id' => $adminUserId, 'grid_key' => $gridKey]);

        $bookmarks = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            try {
                $state = json_decode((string) $row['state_json'], true, flags: JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $state = [];
            }
            $bookmarks[] = [
                'id' => (int) $row['id'],
                'name' => (string) $row['name'],
                'state' => is_array($state) ? $state : [],
                'is_default' => (bool) $row['is_default'],
            ];
        }

        return $bookmarks;
    }

    /** @param array<string, mixed> $state */
    public function save(
        int $adminUserId,
        string $gridKey,
        string $name,
        array $state,
        bool $isDefault = false,
    ): void {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Grid bookmark name cannot be empty.');
        }

        $this->pdo->beginTransaction();
        try {
            if ($isDefault) {
                $clear = $this->pdo->prepare(
                    'UPDATE admin_grid_bookmarks SET is_default = 0 '
                    . 'WHERE admin_user_id = :user_id AND grid_key = :grid_key',
                );
                $clear->execute(['user_id' => $adminUserId, 'grid_key' => $gridKey]);
            }

            $existing = $this->pdo->prepare(
                'SELECT id FROM admin_grid_bookmarks '
                . 'WHERE admin_user_id = :user_id AND grid_key = :grid_key AND name = :name '
                . 'LIMIT 1',
            );
            $existing->execute([
                'user_id' => $adminUserId,
                'grid_key' => $gridKey,
                'name' => $name,
            ]);
            $id = $existing->fetchColumn();
            $payload = json_encode($state, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
            $now = gmdate('Y-m-d H:i:s');

            if ($id !== false) {
                $update = $this->pdo->prepare(
                    'UPDATE admin_grid_bookmarks '
                    . 'SET state_json = :state_json, is_default = :is_default, updated_at = :updated_at '
                    . 'WHERE id = :id',
                );
                $update->execute([
                    'state_json' => $payload,
                    'is_default' => $isDefault ? 1 : 0,
                    'updated_at' => $now,
                    'id' => (int) $id,
                ]);
            } else {
                $insert = $this->pdo->prepare(
                    'INSERT INTO admin_grid_bookmarks '
                    . '(admin_user_id, grid_key, name, state_json, is_default, created_at, updated_at) '
                    . 'VALUES (:user_id, :grid_key, :name, :state_json, :is_default, :created_at, :updated_at)',
                );
                $insert->execute([
                    'user_id' => $adminUserId,
                    'grid_key' => $gridKey,
                    'name' => $name,
                    'state_json' => $payload,
                    'is_default' => $isDefault ? 1 : 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function delete(int $adminUserId, string $gridKey, int $bookmarkId): void
    {
        $statement = $this->pdo->prepare(
            'DELETE FROM admin_grid_bookmarks '
            . 'WHERE id = :id AND admin_user_id = :user_id AND grid_key = :grid_key',
        );
        $statement->execute([
            'id' => $bookmarkId,
            'user_id' => $adminUserId,
            'grid_key' => $gridKey,
        ]);
    }
}
