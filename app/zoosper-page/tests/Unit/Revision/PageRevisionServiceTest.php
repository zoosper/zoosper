<?php
declare(strict_types=1);
use Zoosper\Page\Repository\PageRevisionRepository;use Zoosper\Page\Service\PageRevisionService;
it('requires a complete restorable snapshot',function():void{$pdo=new PDO('sqlite::memory:');$pdo->exec('CREATE TABLE page_revisions (id INTEGER PRIMARY KEY AUTOINCREMENT,page_id INTEGER,title TEXT,slug TEXT,content TEXT,status TEXT,content_format TEXT,content_json TEXT,meta_title TEXT,meta_description TEXT,meta_keywords TEXT,canonical_url TEXT,created_by INTEGER,created_at TEXT)');$service=new PageRevisionService(new PageRevisionRepository($pdo),2);expect(fn()=>$service->capture(1,['title'=>'Missing fields'],1))->toThrow(RuntimeException::class);});










