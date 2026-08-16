<?php

declare(strict_types=1);

it('owns Page save orchestration outside Admin and shares one container service',function():void{$root=dirname(__DIR__,3);$coordinator=$root.'/src/Application/Save/PageSaveCoordinator.php';$admin=(string)file_get_contents($root.'/src/Admin/Controller/PageAdminController.php');$services=(string)file_get_contents($root.'/config/services.php');$controllers=(string)file_get_contents($root.'/config/controllers.php');expect($coordinator)->toBeFile()->and($root.'/src/Admin/Save/PageSaveCoordinator.php')->not->toBeFile()->and($admin)->toContain('Application\\Save\\PageSaveCoordinator')->and($services)->toContain('PageSaveCoordinator::class =>')->and($controllers)->toContain('pageSaver: $services->get(PageSaveCoordinator::class)');});
