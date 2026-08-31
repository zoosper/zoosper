<?php
declare(strict_types=1);
namespace Zoosper\Menu\Contract;
use Zoosper\Menu\Model\{Menu,MenuItem};
interface MenuAdminRepositoryInterface {
 /** @return list<Menu> */ public function all(): array;
 public function find(int $id): ?Menu;
 /** @return list<MenuItem> */ public function items(int $menuId): array;
 public function saveMenu(?int $id,int $siteId,string $code,string $label,string $status): int;
 public function saveItem(?int $id,int $menuId,?int $parentId,?int $pageId,string $label,?string $url,string $target,int $position,string $status): int;
 public function deleteMenu(int $id): void;
 public function deleteItem(int $id): void;
}










