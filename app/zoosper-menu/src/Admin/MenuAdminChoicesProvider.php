<?php
declare(strict_types=1);
namespace Zoosper\Menu\Admin;
use PDO;
/** Read-only option source for menu forms; mutation rules remain in the application service. */
final readonly class MenuAdminChoicesProvider {
 public function __construct(private PDO $pdo){}
 /** @return list<array{id:int,label:string}> */ public function sites(): array { $rows=$this->pdo->query('SELECT id,name FROM sites ORDER BY name,id')->fetchAll(PDO::FETCH_ASSOC); return array_map(static fn(array $r):array=>['id'=>(int)$r['id'],'label'=>(string)$r['name']],$rows); }
 /** @return list<array{id:int,label:string}> */ public function pages(int $siteId): array { $s=$this->pdo->prepare("SELECT id,title FROM pages WHERE site_id=:site AND status='published' ORDER BY title,id");$s->execute(['site'=>$siteId]);return array_map(static fn(array $r):array=>['id'=>(int)$r['id'],'label'=>(string)$r['title']],$s->fetchAll(PDO::FETCH_ASSOC)); }
}










