<?php

declare(strict_types=1);
namespace Zoosper\UrlRewrite\Service;
use InvalidArgumentException;use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;
final readonly class RedirectChainInspector
{
 public function __construct(private UrlRewriteRepository $rewrites,private int $maxDepth=10){}
 /** @return list<string> */ public function inspect(int $siteId,string $source,string $target):array{$chain=[$source];$seen=[$source=>true];$current=$target;for($depth=0;$depth<$this->maxDepth;$depth++){$chain[]=$current;if(isset($seen[$current]))throw new InvalidArgumentException('Redirect cycle detected: '.implode(' -> ',$chain));$seen[$current]=true;if(preg_match('#^https?://#i',$current)===1)return $chain;$next=$this->rewrites->findActiveByRequestPath($siteId,$current);if($next===null)return $chain;$current=$next->targetPath;}throw new InvalidArgumentException('Redirect chain exceeds maximum depth of '.$this->maxDepth.': '.implode(' -> ',$chain));}
}
