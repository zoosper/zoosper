<?php
declare(strict_types=1);
namespace Zoosper\Menu\Contract;
use Zoosper\Menu\Model\MenuItem;
interface MenuItemRepositoryInterface { /** @return list<MenuItem> */ public function activeForMenu(int $menuId): array; public function wouldCreateCycle(int $menuId,int $itemId,?int $parentId): bool; }










