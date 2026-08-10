<?php
declare(strict_types=1);
namespace Zoosper\Menu\Service;
use RuntimeException; use Zoosper\Menu\Model\MenuItem; use Zoosper\Menu\Tree\MenuNode;
final class MenuTreeBuilder {
 /** @param list<MenuItem> $items @param array<int,string> $pageUrls @return list<MenuNode> */
 public function build(array $items,array $pageUrls,string $currentPath='/',int $maxDepth=20): array{$byParent=[];foreach($items as $i)$byParent[$i->parentId??0][]=$i;$walk=function(int $parent,int $depth,array $trail)use(&$walk,$byParent,$pageUrls,$currentPath,$maxDepth):array{if($depth>$maxDepth)throw new RuntimeException('Menu tree exceeds maximum depth.');$nodes=[];foreach($byParent[$parent]??[] as $item){if(isset($trail[$item->id]))throw new RuntimeException('Menu tree contains a cycle.');$href=$item->pageId!==null?($pageUrls[$item->pageId]??'#'):$this->safeExternalHref($item->url);$next=$trail;$next[$item->id]=true;$nodes[]=new MenuNode($item,$href,$href!=='#'&&rtrim($href,'/')===rtrim($currentPath,'/'),$walk($item->id,$depth+1,$next));}return $nodes;};return $walk(0,1,[]);}

 private function safeExternalHref(?string $url): string
 {
  if($url===null) return '#';
  $url=trim(html_entity_decode($url,ENT_QUOTES|ENT_HTML5,'UTF-8'));
  if($url===''||str_contains($url,'<')||str_contains($url,'>')||str_contains($url,'"')||str_contains($url,chr(39))||str_contains($url,'`')||str_contains($url,',')) return '#';
  if(str_starts_with($url,'/')) return str_starts_with($url,'//')?'#':$url;
  $parts=parse_url($url);
  if(!is_array($parts)||!in_array(strtolower((string)($parts['scheme']??'')),['http','https'],true)||empty($parts['host'])) return '#';
  return $url;
 }
}
