<?php

declare(strict_types=1);
namespace Zoosper\UrlRewrite\Routing;
use Zoosper\Core\Http\Request;use Zoosper\Core\Http\Response;use Zoosper\Core\Routing\FallbackHandlerInterface;use Zoosper\UrlRewrite\Service\UrlRewriteResolver;
final readonly class UrlRewriteFallbackHandler implements FallbackHandlerInterface
{
 public function __construct(private UrlRewriteResolver $resolver,private FallbackHandlerInterface $next){}
 public function supports(object $request):bool{return $request instanceof Request&&$request->method()==='GET'&&$request->siteContext()?->siteId!==null&&!$this->reserved($request->path());}
 public function handle(object $request):mixed{if(!$request instanceof Request||!$this->supports($request))return $this->next->handle($request);$rewrite=$this->resolver->resolve((int)$request->siteContext()->siteId,$request->path());return $rewrite===null?$this->next->handle($request):Response::redirect($rewrite->targetPath,$rewrite->redirectType);}
 private function reserved(string $path):bool{foreach(['/admin','/api','/assets','/static','/sitemap.xml','/robots.txt'] as $p){if($path===$p||str_starts_with($path,$p.'/'))return true;}return false;}
}










