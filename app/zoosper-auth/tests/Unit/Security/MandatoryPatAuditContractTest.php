<?php
declare(strict_types=1);
it('requires audit infrastructure for PAT issue and revocation',function():void{$root=dirname(__DIR__,5);$controller=file_get_contents($root.'/app/zoosper-auth/src/Admin/Controller/PersonalAccessTokenAdminController.php');$services=file_get_contents($root.'/app/zoosper-auth/config/controllers.php');expect($controller)->toContain('private AuditLoggerInterface $audit')->toContain('$this->audit->logAction(')->not->toContain('$this->audit?->logAction(')->and($services)->toContain('$services->get(AuditLoggerInterface::class)');});
