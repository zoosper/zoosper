<?php

declare(strict_types=1);

use Zoosper\Menu\Model\MenuItem;
use Zoosper\Menu\Service\MenuTreeBuilder;

it('does not expose historical rich-text markup as a navigation href',function(){
 $item=new MenuItem(1,1,null,null,'Docs','<a href="https://docs.zoosper.com">https://docs.zoosper.com</a>','_blank',0,'active');
 $tree=(new MenuTreeBuilder())->build([$item],[],'/');
 expect($tree[0]->href)->toBe('#')->and($tree[0]->toArray()['href'])->toBe('#');
});

it('continues to expose valid absolute and relative external links',function(){
 $items=[
  new MenuItem(1,1,null,null,'Docs','https://docs.zoosper.com','_blank',0,'active'),
  new MenuItem(2,1,null,null,'Home','/','_self',1,'active'),
 ];
 $tree=(new MenuTreeBuilder())->build($items,[],'/');
 expect($tree[0]->href)->toBe('https://docs.zoosper.com')->and($tree[1]->href)->toBe('/');
});










