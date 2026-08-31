<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface; use Zoosper\Core\Console\ConsoleOptions; use Zoosper\Core\Console\ConsoleOutput; use Zoosper\Core\Scaffold\ConsoleCommandScaffolder;
final readonly class MakeConsoleCommand implements ConsoleCommandInterface
{
 public function __construct(private ConsoleCommandScaffolder $scaffolder) {} public function name(): string{return 'make:command';} public function description(): string{return 'Scaffold a module-owned console command.';}
 public function run(array $args, ConsoleOutput $output): int { $o=ConsoleOptions::parse($args); $r=$this->scaffolder->scaffold($args[0]??'', $args[1]??'', ConsoleOptions::required($o,'name'), $o['description']??''); $output->writeln("Created console command {$r->commandName}"); $output->writeln("Module: {$r->moduleName}"); $output->writeln("Class: {$r->commandClass}"); $output->writeln('Files:'); foreach($r->createdFiles as $f){$output->writeln("  - {$f}");} $output->writeln("Run it with: php bin/zoosper {$r->commandName}"); return 0; }
}










