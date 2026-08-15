<?php

declare(strict_types=1);

use Zoosper\Page\Repository\PageRepository;

it('lists Pages only for the requested Site', function (): void {
    $pdo=new PDO('sqlite::memory:');$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER,title TEXT,slug TEXT,content TEXT,status TEXT,created_by INTEGER,updated_by INTEGER,created_at TEXT,updated_at TEXT,published_at TEXT,content_format TEXT,content_json TEXT,meta_title TEXT,meta_description TEXT,meta_keywords TEXT,canonical_url TEXT)');
    $repo=new PageRepository($pdo);$repo->create(1,'One','one','one');$repo->create(2,'Two','two','two');
    expect($repo->allForSite(1))->toHaveCount(1)->and($repo->allForSite(1)[0]->title)->toBe('One');
});
