<?php declare(strict_types=1);it('exposes shared atomic Site create and update',function(){$root=dirname(__DIR__,3);$routes=(string)file_get_contents($root.'/config/api_routes.php');$service=(string)file_get_contents($root.'/src/Application/SiteMutationService.php');expect($routes)->toContain("'action'=>'create'")->toContain("'action'=>'update'")->and($service)->toContain('beginTransaction')->toContain('rollBack');});










