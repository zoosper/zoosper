<?php
declare(strict_types=1);
it('runs the production security policy before console service composition',function():void{$s=file_get_contents(dirname(__DIR__,5).'/app/zoosper-core/src/Console/ConsoleServiceFactory.php');expect($s)->toContain('public function __construct')->toContain('ProductionSecurityPolicy::assertEnvironment();');});
