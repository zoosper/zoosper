<?php
declare(strict_types=1);
namespace Zoosper\Menu\Repository;
use PDO; use Zoosper\Menu\Contract\MenuRepositoryInterface; use Zoosper\Menu\Model\Menu;
final readonly class PdoMenuRepository implements MenuRepositoryInterface { public function __construct(private PDO $pdo){} public function findActiveBySiteAndCode(int $siteId,string $code): ?Menu{$s=$this->pdo->prepare("SELECT * FROM menus WHERE site_id=:site AND code=:code AND status='active' LIMIT 1");$s->execute(['site'=>$siteId,'code'=>$code]);$r=$s->fetch(PDO::FETCH_ASSOC);return is_array($r)?new Menu((int)$r['id'],(int)$r['site_id'],(string)$r['code'],(string)$r['label'],(string)$r['status'],(string)$r['created_at'],(string)$r['updated_at']):null;} }










