<?php
declare(strict_types=1);
namespace Zoosper\Menu\Repository;
use PDO; use Zoosper\Menu\Contract\MenuItemRepositoryInterface; use Zoosper\Menu\Model\MenuItem;
final readonly class PdoMenuItemRepository implements MenuItemRepositoryInterface {
 public function __construct(private PDO $pdo){}
 public function activeForMenu(int $menuId): array{$s=$this->pdo->prepare("SELECT * FROM menu_items WHERE menu_id=:menu AND status='active' ORDER BY position,id");$s->execute(['menu'=>$menuId]);return array_map(fn(array $r)=>$this->hydrate($r),$s->fetchAll(PDO::FETCH_ASSOC));}
 public function wouldCreateCycle(int $menuId,int $itemId,?int $parentId): bool{if($parentId===null)return false;if($parentId===$itemId)return true;$seen=[];$current=$parentId;$s=$this->pdo->prepare('SELECT parent_id FROM menu_items WHERE id=:id AND menu_id=:menu');while($current!==null){if(isset($seen[$current])||$current===$itemId)return true;$seen[$current]=true;$s->execute(['id'=>$current,'menu'=>$menuId]);$v=$s->fetchColumn();$current=$v===false||$v===null?null:(int)$v;}return false;}
 private function hydrate(array $r): MenuItem{return new MenuItem((int)$r['id'],(int)$r['menu_id'],$r['parent_id']===null?null:(int)$r['parent_id'],$r['page_id']===null?null:(int)$r['page_id'],(string)$r['label'],$r['url']===null?null:(string)$r['url'],(string)$r['target'],(int)$r['position'],(string)$r['status']);}
}
