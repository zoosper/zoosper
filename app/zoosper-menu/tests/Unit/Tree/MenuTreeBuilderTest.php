<?php
declare(strict_types=1);
use Zoosper\Menu\Model\MenuItem; use Zoosper\Menu\Service\MenuTreeBuilder;
it('builds deterministic nested menu trees and active state',function(){ $items=[new MenuItem(1,1,null,null,'Home','/','_self',10,'active'),new MenuItem(2,1,null,null,'About','/about','_self',20,'active'),new MenuItem(3,1,2,null,'Team','/about/team','_self',10,'active')];$tree=(new MenuTreeBuilder())->build($items,[],'/about/team');expect($tree)->toHaveCount(2)->and($tree[1]->children)->toHaveCount(1)->and($tree[1]->children[0]->active)->toBeTrue();});
