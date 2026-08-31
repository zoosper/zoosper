<?php
declare(strict_types=1);
namespace Zoosper\Menu\Service;
use Zoosper\Menu\Contract\{BreadcrumbProviderInterface,MenuProviderInterface}; use Zoosper\Menu\Tree\MenuNode;
final readonly class BreadcrumbProvider implements BreadcrumbProviderInterface { public function __construct(private MenuProviderInterface $menus){} public function breadcrumbs(int $siteId,string $menuCode,string $currentPath): array{$find=function(array $nodes,array $trail=[])use(&$find):array{foreach($nodes as $node){$next=[...$trail,['label'=>$node->item->label,'href'=>$node->href]];if($node->active)return $next;$found=$find($node->children,$next);if($found!==[])return $found;}return [];};return $find($this->menus->tree($siteId,$menuCode,$currentPath));} }










