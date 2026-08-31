<?php
declare(strict_types=1);
it('extracts deployment and scaffolding behaviour from the executable', function (): void {
 $root=dirname(__DIR__,5); $bin=(string)file_get_contents($root.'/bin/zoosper');
 foreach(['DeployCommand','MakeModuleCommand','MakePackageModuleCommand','MakeConsoleCommand'] as $class){expect($root.'/app/zoosper-core/src/Console/BuiltIn/'.$class.'.php')->toBeFile()->and($bin)->toContain('new '.$class.'(');}
 expect($bin)->not->toContain("if (\$command === 'deploy')")->not->toContain("if (\$command === 'make:module')")->not->toContain('function parseOptions(')->not->toContain('function required(');
});
it('keeps deploy verification before completion in its command owner', function (): void {
 $source=(string)file_get_contents(dirname(__DIR__,5).'/app/zoosper-core/src/Console/BuiltIn/DeployCommand.php');
 expect(strpos($source,'Module manifest post-compile verification passed.'))->toBeLessThan(strpos($source,'== Deploy complete =='));
});










