<?php

declare(strict_types=1);
namespace Zoosper\UrlRewrite\Service;
use InvalidArgumentException;
final readonly class RedirectPolicy
{
    public function validate(string $source,string $target,int $type):array
    {
        $source=$this->source($source);$target=$this->target($target);
        if(!in_array($type,[301,302],true))throw new InvalidArgumentException('Redirect type must be 301 or 302.');
        if($source===$target)throw new InvalidArgumentException('Redirect source and target must differ.');
        foreach(['/admin','/api','/assets','/static','/sitemap.xml','/robots.txt'] as $prefix){if($source===$prefix||str_starts_with($source,$prefix.'/'))throw new InvalidArgumentException('Redirect source targets a reserved application path.');}
        return ['source'=>$source,'target'=>$target,'type'=>$type];
    }
    public function source(string $path):string{$path='/'.trim((string)(parse_url(trim($path),PHP_URL_PATH)?:''),'/');if($path==='/')throw new InvalidArgumentException('The Site root cannot be used as a redirect source.');return $path;}
    public function target(string $target):string{$target=trim($target);if($target==='')throw new InvalidArgumentException('Redirect target is required.');$scheme=strtolower((string)parse_url($target,PHP_URL_SCHEME));if($scheme!==''&&!in_array($scheme,['http','https'],true))throw new InvalidArgumentException('Absolute redirect targets must use HTTP or HTTPS.');return $scheme===''?'/'.trim((string)(parse_url($target,PHP_URL_PATH)?:''),'/'):$target;}
}










