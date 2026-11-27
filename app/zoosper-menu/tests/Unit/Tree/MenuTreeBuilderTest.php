<?php
declare(strict_types=1);
use Zoosper\Menu\Model\MenuItem; use Zoosper\Menu\Service\MenuTreeBuilder;
it('builds deterministic nested menu trees and active state',function(){ $items=[new MenuItem(1,1,null,null,'Home','/','_self',10,'active'),new MenuItem(2,1,null,null,'About','/about','_self',20,'active'),new MenuItem(3,1,2,null,'Team','/about/team','_self',10,'active')];$tree=(new MenuTreeBuilder())->build($items,[],'/about/team');expect($tree)->toHaveCount(2)->and($tree[1]->children)->toHaveCount(1)->and($tree[1]->children[0]->active)->toBeTrue();});

it('allows a leaf at the maximum depth but rejects deeper trees',function(){
 $items=[new MenuItem(1,1,null,null,'Root','/','_self',0,'active'),new MenuItem(2,1,1,null,'Child','/child','_self',0,'active')];
 expect((new MenuTreeBuilder())->build($items,[],'/',2)[0]->children)->toHaveCount(1);
 $items[]=new MenuItem(3,1,2,null,'Grandchild','/child/deep','_self',0,'active');
 expect(fn()=>(new MenuTreeBuilder())->build($items,[],'/',2))->toThrow(RuntimeException::class,'maximum depth');
});










