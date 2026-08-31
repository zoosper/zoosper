<?php
declare(strict_types=1);
it('keeps session me read-only and login explicitly rate limited',function():void{$root=dirname(__DIR__,5);$me=file_get_contents($root.'/app/zoosper-api/src/Controller/MeController.php');$login=file_get_contents($root.'/app/zoosper-api/src/Controller/AuthController.php');expect($me)->toContain('public function show(')->not->toContain('public function update(')->not->toContain('public function delete(')->and($login)->toContain('checkPasswordLogin(')->toContain('too_many_attempts');});










