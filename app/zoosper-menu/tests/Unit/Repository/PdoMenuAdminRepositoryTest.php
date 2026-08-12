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
it('updates a Menu item with exactly the update placeholders',function(){
 $pdo=new PDO('sqlite::memory:');$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
 $pdo->exec('CREATE TABLE menu_items(id INTEGER PRIMARY KEY AUTOINCREMENT,menu_id INTEGER,parent_id INTEGER NULL,page_id INTEGER NULL,label TEXT,url TEXT NULL,target TEXT,position INTEGER,status TEXT,created_at TEXT,updated_at TEXT)');
 $pdo->exec("INSERT INTO menu_items(menu_id,parent_id,page_id,label,url,target,position,status,created_at,updated_at) VALUES(1,NULL,NULL,'Old','/old','_self',2,'active','now','now')");
 $rules=new class implements \Zoosper\Menu\Contract\MenuItemRepositoryInterface{public function activeForMenu(int $menuId):array{return [];}public function wouldCreateCycle(int $menuId,int $itemId,?int $parentId):bool{return false;}};
 (new \Zoosper\Menu\Repository\PdoMenuAdminRepository($pdo,$rules))->saveItem(1,1,null,4,'Home',null,'_self',0,'active');
 $row=$pdo->query('SELECT * FROM menu_items WHERE id=1')->fetch(PDO::FETCH_ASSOC);
 expect($row['label'])->toBe('Home')->and((int)$row['page_id'])->toBe(4)->and($row['url'])->toBeNull()->and((int)$row['position'])->toBe(0);
});
