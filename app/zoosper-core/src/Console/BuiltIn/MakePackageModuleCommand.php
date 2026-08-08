<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface; use Zoosper\Core\Console\ConsoleOutput; use Zoosper\Core\Scaffold\PackageModuleScaffolder;
final readonly class MakePackageModuleCommand implements ConsoleCommandInterface
{
 public function __construct(private PackageModuleScaffolder $scaffolder) {} public function name(): string{return 'make:package-module';} public function description(): string{return 'Scaffold a Composer package module.';}
 public function run(array $args, ConsoleOutput $output): int { $r=$this->scaffolder->scaffold($args[0]??''); $output->writeln("Created package module {$r->moduleName}"); $output->writeln("Package: {$r->packageName}"); $output->writeln("Namespace: {$r->namespace}"); $output->writeln("Path: {$r->packagePath}"); $output->writeln('Files:'); foreach($r->createdFiles as $f){$output->writeln("  - {$f}");} $output->writeln("Next: run PHP=php8.5 composer dump-autoload and vendor/bin/pest {$r->packagePath}/tests/Unit."); return 0; }
}
