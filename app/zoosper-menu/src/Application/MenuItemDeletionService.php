<?php

declare(strict_types=1);

namespace Zoosper\Menu\Application;

use RuntimeException;
use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
use Zoosper\Menu\Lifecycle\MenuReferenceInspector;

/** Prevents cross-Menu and parent-cascade item deletion through every Menu mutation adapter. */
final readonly class MenuItemDeletionService
{
    public function __construct(private MenuAdminRepositoryInterface $menus, private MenuReferenceInspector $references) {}
    public function delete(int $menuId,int $itemId): void
    {
        if(!$this->references->itemBelongsToMenu($menuId,$itemId)){throw new RuntimeException('Menu item does not belong to this Menu.');}
        $children=$this->references->childCount($menuId,$itemId);
        if($children>0){throw new RuntimeException("Move or remove {$children} child Menu item(s) before deletion.");}
        $this->menus->deleteItem($itemId);
    }
}
