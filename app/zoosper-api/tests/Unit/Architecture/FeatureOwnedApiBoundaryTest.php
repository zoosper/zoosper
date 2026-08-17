<?php

declare(strict_types=1);

it('keeps the API platform free of Page and Menu feature ownership',function():void{
 $root=dirname(__DIR__,5);$paths=array_merge(glob($root.'/app/zoosper-api/src/**/*.php')?:[],glob($root.'/app/zoosper-api/config/*.php')?:[]);$source='';foreach($paths as $path)$source.=(string)file_get_contents($path);
 expect($source)->not->toContain('Zoosper\\Page\\')->not->toContain('Zoosper\\Menu\\')->not->toContain('Zoosper\\Media\\')->not->toContain('/api/v1/pages')->not->toContain('/api/v1/menus')->not->toContain('/api/v1/media');
 expect($root.'/app/zoosper-page/src/Api/PageApiController.php')->toBeFile()->and($root.'/app/zoosper-menu/src/Api/MenuApiController.php')->toBeFile();
});
it('makes feature route disappearance a consequence of module removal',function():void{
 $root=dirname(__DIR__,5);$platform=(string)file_get_contents($root.'/app/zoosper-api/config/api_routes.php');$page=(string)file_get_contents($root.'/app/zoosper-page/config/api_routes.php');$menu=(string)file_get_contents($root.'/app/zoosper-menu/config/api_routes.php');
 expect($platform)->toContain('/api/v1/health')->not->toContain('/api/v1/pages')->not->toContain('/api/v1/menus')->and($page)->toContain('/api/v1/pages')->and($menu)->toContain('/api/v1/menus');
});
