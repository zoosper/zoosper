<?php

declare(strict_types=1);

use Zoosper\Site\Lifecycle\SiteLifecycleCoordinator;
use Zoosper\Site\Lifecycle\SiteReferenceInspector;
use Zoosper\Site\Repository\SiteRepository;

function siteLifecycleDb(): PDO { $pdo=new PDO('sqlite::memory:');$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$pdo->exec('CREATE TABLE sites(id INTEGER PRIMARY KEY AUTOINCREMENT,code TEXT UNIQUE,name TEXT,status TEXT,homepage_slug TEXT,theme_code TEXT,locale TEXT,currency TEXT,base_url TEXT,website_code TEXT,store_code TEXT,store_view_code TEXT,path_prefix TEXT,created_at TEXT,updated_at TEXT)');$pdo->exec('CREATE TABLE site_domains(id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER,host TEXT,is_primary INTEGER,created_at TEXT,updated_at TEXT)');return $pdo; }
it('makes a Site inactive and restores it using existing status vocabulary',function(){ $pdo=siteLifecycleDb();$repo=new SiteRepository($pdo);$id=$repo->create('main','Main','main.test');$life=new SiteLifecycleCoordinator($pdo,$repo,new SiteReferenceInspector($pdo));$life->disable($repo->findById($id),1,'a');expect($repo->findById($id)->status)->toBe('inactive');$life->restore($repo->findById($id),1,'a');expect($repo->findById($id)->status)->toBe('active');});
it('blocks permanent deletion until inactive and completely unreferenced',function(){ $pdo=siteLifecycleDb();$repo=new SiteRepository($pdo);$id=$repo->create('main','Main','main.test');$life=new SiteLifecycleCoordinator($pdo,$repo,new SiteReferenceInspector($pdo));expect($life->deletePermanently($repo->findById($id),1,'a')->successful)->toBeFalse();$life->disable($repo->findById($id),1,'a');$blocked=$life->deletePermanently($repo->findById($id),1,'a');expect($blocked->successful)->toBeFalse()->and($blocked->blockers['domains'])->toBe(1);});
it('permanently deletes an inactive Site only after references are removed',function(){ $pdo=siteLifecycleDb();$repo=new SiteRepository($pdo);$id=$repo->create('main','Main','main.test');$life=new SiteLifecycleCoordinator($pdo,$repo,new SiteReferenceInspector($pdo));$life->disable($repo->findById($id),1,'a');$pdo->exec('DELETE FROM site_domains WHERE site_id='.$id);expect($life->deletePermanently($repo->findById($id),1,'a')->successful)->toBeTrue()->and($repo->findById($id))->toBeNull();});
