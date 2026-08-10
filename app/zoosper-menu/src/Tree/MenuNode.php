<?php
declare(strict_types=1);
namespace Zoosper\Menu\Tree;
use Zoosper\Menu\Model\MenuItem;
final readonly class MenuNode { /** @param list<MenuNode> $children */ public function __construct(public MenuItem $item,public string $href,public bool $active,public array $children=[]){} /** @return array<string,mixed> */ public function toArray(): array{return ['id'=>$this->item->id,'label'=>$this->item->label,'href'=>$this->href,'target'=>$this->item->target,'active'=>$this->active,'children'=>array_map(static fn(self $n)=>$n->toArray(),$this->children)];} }
