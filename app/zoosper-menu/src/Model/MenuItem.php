<?php
declare(strict_types=1);
namespace Zoosper\Menu\Model;
use InvalidArgumentException;
final readonly class MenuItem {
 public function __construct(public int $id,public int $menuId,public ?int $parentId,public ?int $pageId,public string $label,public ?string $url,public string $target,public int $position,public string $status){ if($pageId===null && ($url===null||trim($url)==='')) throw new InvalidArgumentException('Menu item requires a page target or external URL.'); if(!in_array($target,['_self','_blank'],true)) throw new InvalidArgumentException('Menu item target must be _self or _blank.'); }
 public function isActive(): bool{return $this->status==='active';}
}
