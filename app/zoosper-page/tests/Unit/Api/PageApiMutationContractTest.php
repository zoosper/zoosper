<?php

declare(strict_types=1);

it('declares stateless scoped create and update without Admin namespace coupling',function():void{$root=dirname(__DIR__,3);$routes=require $root.'/config/api_routes.php';$map=[];foreach($routes as $r)$map[$r['method'].' '.$r['path']]=$r;$source=(string)file_get_contents($root.'/src/Api/PageApiController.php');expect($map)->toHaveKeys(['POST /api/v1/pages','PATCH /api/v1/pages/{id:\d+}'])->and($map['POST /api/v1/pages']['stateless'])->toBeTrue()->and($map['PATCH /api/v1/pages/{id:\d+}']['stateless'])->toBeTrue()->and($source)->toContain("'pages:write'")->toContain("can('page.manage')")->toContain('page.api_created')->toContain('page.api_updated')->toContain('DocumentNormalizer')->not->toContain('BlockJsonToHtmlRenderer')->not->toContain('Zoosper\Page\Admin')->not->toContain('tokenHash')->not->toContain("'content_json'=>\$body['content_json']");});










