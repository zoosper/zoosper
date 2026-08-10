<?php

declare(strict_types=1);

use Zoosper\Menu\Application\MenuAdminService;
use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
use Zoosper\Menu\Model\{Menu,MenuItem};

function menuUrlContractRepository(): MenuAdminRepositoryInterface
{
    return new class implements MenuAdminRepositoryInterface {
        public ?string $url = null;
        public function all(): array{return [];} public function find(int $id): ?Menu{return null;} public function items(int $menuId): array{return [];}
        public function saveMenu(?int $id,int $siteId,string $code,string $label,string $status): int{return 1;}
        public function saveItem(?int $id,int $menuId,?int $parentId,?int $pageId,string $label,?string $url,string $target,int $position,string $status): int{$this->url=$url;return 1;}
        public function deleteMenu(int $id): void{} public function deleteItem(int $id): void{}
    };
}

it('accepts plain relative and absolute HTTP URLs',function(){
 $repo=menuUrlContractRepository();$service=new MenuAdminService($repo);
 $service->saveItem(1,['label'=>'Docs','url'=>'https://docs.zoosper.com']);expect($repo->url)->toBe('https://docs.zoosper.com');
 $service->saveItem(1,['label'=>'Home','url'=>'/']);expect($repo->url)->toBe('/');
});

it('rejects pasted rich-text anchor markup',function(){
 $service=new MenuAdminService(menuUrlContractRepository());
 expect(fn()=>$service->saveItem(1,['label'=>'Docs','url'=>'<a href="https://docs.zoosper.com">https://docs.zoosper.com</a>']))->toThrow(InvalidArgumentException::class,'plain text');
});

it('documents that menu API lookup is request-site scoped',function(){
 $root=dirname(__DIR__,3);$source=(string)file_get_contents($root.'/src/Api/MenuController.php');
 expect($source)->toContain("'site_scoped'=>true")->toContain('resolved request site and code');
});
