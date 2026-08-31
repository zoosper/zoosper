<?php

declare(strict_types=1);

namespace Zoosper\Menu\Application;

use PDO;
use RuntimeException;
use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
use Zoosper\Menu\Model\Menu;

/** Enforces request-Site and same-Menu relationships before shared mutations. */
final readonly class MenuMutationGuard
{
    public function __construct(private PDO $pdo, private MenuAdminRepositoryInterface $menus) {}
    public function siteMenu(int $menuId,int $siteId): Menu { $menu=$this->menus->find($menuId);if($menu===null||$menu->siteId!==$siteId)throw new RuntimeException('Menu does not exist for this Site.');return $menu; }
    /** @param array<string,mixed> $input */
    public function itemInput(int $menuId,int $siteId,array $input,?int $itemId=null): array
    {
        $this->siteMenu($menuId,$siteId);
        if($itemId!==null&&!$this->itemBelongs($menuId,$itemId))throw new RuntimeException('Menu item does not belong to this Menu.');
        $parent=(int)($input['parent_id']??0);if($parent>0&&!$this->itemBelongs($menuId,$parent))throw new RuntimeException('Parent Menu item does not belong to this Menu.');
        $page=(int)($input['page_id']??0);if($page>0&&!$this->publishedPageBelongs($page,$siteId))throw new RuntimeException('Page target must be published in the same Site.');
        return $input;
    }
    private function itemBelongs(int $menuId,int $itemId):bool{$s=$this->pdo->prepare('SELECT COUNT(*) FROM menu_items WHERE id=:item AND menu_id=:menu');$s->execute(['item'=>$itemId,'menu'=>$menuId]);return(int)$s->fetchColumn()===1;}
    private function publishedPageBelongs(int $pageId,int $siteId):bool{$s=$this->pdo->prepare("SELECT COUNT(*) FROM pages WHERE id=:page AND site_id=:site AND status='published'");$s->execute(['page'=>$pageId,'site'=>$siteId]);return(int)$s->fetchColumn()===1;}
}










