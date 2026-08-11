<?php

declare(strict_types=1);

use Zoosper\Page\Lifecycle\PageLifecycleCoordinator;
use Zoosper\Page\Lifecycle\PageReferenceInspector;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Repository\PageRevisionRepository;
use Zoosper\Page\Service\PageRevisionService;

function pageLifecycleDb(): PDO {
    $pdo=new PDO('sqlite::memory:'); $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); $pdo->exec('PRAGMA foreign_keys=ON');
    $pdo->exec('CREATE TABLE pages(id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER,title TEXT,slug TEXT,content TEXT,status TEXT,created_by INTEGER,updated_by INTEGER,created_at TEXT,updated_at TEXT,published_at TEXT)');
    $pdo->exec('CREATE TABLE page_revisions(id INTEGER PRIMARY KEY AUTOINCREMENT,page_id INTEGER,title TEXT,slug TEXT,content TEXT,status TEXT,content_format TEXT,content_json TEXT,meta_title TEXT,meta_description TEXT,meta_keywords TEXT,canonical_url TEXT,created_by INTEGER,created_at TEXT,FOREIGN KEY(page_id) REFERENCES pages(id) ON DELETE CASCADE)');
    $pdo->exec('CREATE TABLE menu_items(id INTEGER PRIMARY KEY,page_id INTEGER NULL)');
    $pdo->exec('CREATE TABLE url_rewrites(id INTEGER PRIMARY KEY,entity_type TEXT,entity_id INTEGER NULL)'); return $pdo;
}
function pageLifecycleFixture(PDO $pdo,string $status='published'): array { $pages=new PageRepository($pdo);$id=$pages->create(1,'Lifecycle','lifecycle','body',$status,7);$revisions=new PageRevisionRepository($pdo);return [$pages->findById($id),new PageLifecycleCoordinator($pdo,$pages,new PageRevisionService($revisions,50),$revisions,new PageReferenceInspector($pdo)),$revisions]; }

it('archives with a safety revision and restores archived pages to draft',function(){ $pdo=pageLifecycleDb();[$page,$life,$revisions]=pageLifecycleFixture($pdo);$archived=$life->archive($page,9,'admin@example.test');expect($archived->successful)->toBeTrue()->and($revisions->forPage($page->id))->toHaveCount(1);$stored=(new PageRepository($pdo))->findById($page->id);expect($stored->status)->toBe('archived');$restored=$life->restore($stored,9,'admin@example.test');expect($restored->successful)->toBeTrue()->and((new PageRepository($pdo))->findById($page->id)->status)->toBe('draft')->and($revisions->forPage($page->id))->toHaveCount(2); });
it('blocks permanent deletion until archived and unreferenced',function(){ $pdo=pageLifecycleDb();[$page,$life]=pageLifecycleFixture($pdo,'draft');expect($life->deletePermanently($page,9,'a')->successful)->toBeFalse();$life->archive($page,9,'a');$archived=(new PageRepository($pdo))->findById($page->id);$pdo->exec("INSERT INTO menu_items(id,page_id) VALUES(1,{$page->id})");$blocked=$life->deletePermanently($archived,9,'a');expect($blocked->successful)->toBeFalse()->and($blocked->blockers['menu_items'])->toBe(1); });
it('deletes an archived unreferenced page and all revisions transactionally',function(){ $pdo=pageLifecycleDb();[$page,$life,$revisions]=pageLifecycleFixture($pdo);$life->archive($page,9,'a');$archived=(new PageRepository($pdo))->findById($page->id);expect($life->deletePermanently($archived,9,'a')->successful)->toBeTrue()->and((new PageRepository($pdo))->findById($page->id))->toBeNull()->and($revisions->forPage($page->id))->toBe([]); });
