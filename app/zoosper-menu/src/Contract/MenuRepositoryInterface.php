<?php
declare(strict_types=1);
namespace Zoosper\Menu\Contract;
use Zoosper\Menu\Model\Menu;
interface MenuRepositoryInterface { public function findActiveBySiteAndCode(int $siteId,string $code): ?Menu; }










