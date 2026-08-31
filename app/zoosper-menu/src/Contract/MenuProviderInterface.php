<?php
declare(strict_types=1);
namespace Zoosper\Menu\Contract;
use Zoosper\Menu\Tree\MenuNode;
interface MenuProviderInterface { /** @return list<MenuNode> */ public function tree(int $siteId,string $code,string $currentPath='/'): array; }










