<?php

declare(strict_types=1);

namespace Zoosper\Menu\Lifecycle;

use PDO;

/** Read-only Menu ownership counts used before irreversible deletion. */
final readonly class MenuReferenceInspector
{
    public function __construct(private PDO $pdo) {}

    /** @return array{menu_items:int} */
    public function counts(int $menuId): array
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM menu_items WHERE menu_id = :id');
        $statement->execute(['id' => $menuId]);
        return ['menu_items' => (int) $statement->fetchColumn()];
    }

    public function childCount(int $menuId, int $itemId): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM menu_items WHERE menu_id = :menu AND parent_id = :item');
        $statement->execute(['menu' => $menuId, 'item' => $itemId]);
        return (int) $statement->fetchColumn();
    }

    public function itemBelongsToMenu(int $menuId, int $itemId): bool
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM menu_items WHERE id = :item AND menu_id = :menu');
        $statement->execute(['item' => $itemId, 'menu' => $menuId]);
        return (int) $statement->fetchColumn() === 1;
    }
}
