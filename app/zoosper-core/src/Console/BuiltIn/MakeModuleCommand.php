<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface; use Zoosper\Core\Console\ConsoleOutput; use Zoosper\Core\Scaffold\ModuleScaffolder;
final readonly class MakeModuleCommand implements ConsoleCommandInterface
{
 public function __construct(private ModuleScaffolder $scaffolder) {} public function name(): string{return 'make:module';} public function description(): string{return 'Scaffold an app module.';}
 public function run(array $args, ConsoleOutput $output): int { $r=$this->scaffolder->scaffold($args[0]??''); $output->writeln("Created module {$r->moduleName}"); $output->writeln("Namespace: {$r->namespace}"); $output->writeln("Path: {$r->modulePath}"); $output->writeln('Files:'); foreach($r->createdFiles as $f){$output->writeln("  - {$f}");} $output->writeln("Next: add the PSR-4 autoload snippet from {$r->modulePath}/README.md if you add PHP classes."); return 0; }
}
