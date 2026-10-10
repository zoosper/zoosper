<?php

declare(strict_types=1);

use Zoosper\Menu\Contract\MenuItemRepositoryInterface;
use Zoosper\Menu\Repository\PdoMenuAdminRepository;
use Zoosper\Pagination\Pager;

it('returns a bounded Site-scoped Menu slice with exact pagination metadata', function (): void {
    $pdo=new PDO('sqlite::memory:');$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE menus (id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER NOT NULL,code TEXT,label TEXT,status TEXT,created_at TEXT,updated_at TEXT)');
    for($i=1;$i<=25;$i++){$site=$i===25?2:1;$label=str_pad((string)$i,2,'0',STR_PAD_LEFT);$pdo->exec("INSERT INTO menus(site_id,code,label,status,created_at,updated_at) VALUES($site,'menu-$i','Menu $label','active','2026-01-01','2026-01-01')");}
    $rules=new class implements MenuItemRepositoryInterface{public function findById(int $id):?\Zoosper\Menu\Model\MenuItem{return null;}public function activeForMenu(int $menuId):array{return [];}public function wouldCreateCycle(int $menuId,int $itemId,?int $parentId):bool{return false;}};
    $result=(new PdoMenuAdminRepository($pdo,$rules))->pageForSite(1,new Pager(2,10));
    expect($result->total)->toBe(24)->and($result->page)->toBe(2)->and($result->pageSize)->toBe(10)->and($result->totalPages())->toBe(3)->and($result->items)->toHaveCount(10)->and($result->items[0]->siteId)->toBe(1)->and($result->items[0]->label)->toBe('Menu 11');
});

it('clamps an out-of-range Menu collection request to the last page', function (): void {
    $pdo=new PDO('sqlite::memory:');$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE menus (id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER NOT NULL,code TEXT,label TEXT,status TEXT,created_at TEXT,updated_at TEXT)');
    $pdo->exec("INSERT INTO menus(site_id,code,label,status,created_at,updated_at) VALUES(1,'main','Main','active','2026-01-01','2026-01-01')");
    $rules=new class implements MenuItemRepositoryInterface{public function findById(int $id):?\Zoosper\Menu\Model\MenuItem{return null;}public function activeForMenu(int $menuId):array{return [];}public function wouldCreateCycle(int $menuId,int $itemId,?int $parentId):bool{return false;}};
    $result=(new PdoMenuAdminRepository($pdo,$rules))->pageForSite(1,new Pager(99999,20));
    expect($result->page)->toBe(1)->and($result->items)->toHaveCount(1);
});
