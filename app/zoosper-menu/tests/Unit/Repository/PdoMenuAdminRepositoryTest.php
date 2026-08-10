<?php
declare(strict_types=1);
use Zoosper\Menu\Repository\PdoMenuAdminRepository;
use Zoosper\Menu\Contract\MenuItemRepositoryInterface;
it('binds exactly the placeholders used when creating a menu',function(){
 $pdo=new PDO('sqlite::memory:');
 $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
 $pdo->exec('CREATE TABLE menus(id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER,code TEXT,label TEXT,status TEXT,created_at TEXT,updated_at TEXT)');
 $rules=new class implements MenuItemRepositoryInterface{public function activeForMenu(int $menuId):array{return [];}public function wouldCreateCycle(int $menuId,int $itemId,?int $parentId):bool{return false;}};
 $id=(new PdoMenuAdminRepository($pdo,$rules))->saveMenu(null,1,'main','Main Navigation','active');
 expect($id)->toBe(1)->and((int)$pdo->query('SELECT site_id FROM menus')->fetchColumn())->toBe(1);
});
