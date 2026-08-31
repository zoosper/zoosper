<?php
declare(strict_types=1);
namespace Zoosper\Menu\Contract;
interface BreadcrumbProviderInterface { /** @return list<array{label:string,href:string}> */ public function breadcrumbs(int $siteId,string $menuCode,string $currentPath): array; }










