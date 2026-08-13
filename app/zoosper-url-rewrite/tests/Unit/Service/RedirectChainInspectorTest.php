<?php
declare(strict_types=1);
use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;use Zoosper\UrlRewrite\Service\RedirectChainInspector;
function chainPdo():PDO{$p=new PDO('sqlite::memory:');$p->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);$p->exec('CREATE TABLE url_rewrites(id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER,request_path TEXT,target_path TEXT,entity_type TEXT,entity_id INTEGER,redirect_type INTEGER,is_active INTEGER,created_at TEXT,updated_at TEXT)');return $p;}
it('detects a Site-scoped multi-hop cycle',function(){$p=chainPdo();$r=new UrlRewriteRepository($p);$r->save(null,1,'b','/c',301);$r->save(null,1,'c','/a',301);expect(fn()=>(new RedirectChainInspector($r))->inspect(1,'/a','/b'))->toThrow(InvalidArgumentException::class,'/a -> /b -> /c -> /a');});
it('stops at a terminal target',function(){$r=new UrlRewriteRepository(chainPdo());expect((new RedirectChainInspector($r))->inspect(1,'/a','/page'))->toBe(['/a','/page']);});
