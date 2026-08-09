<?php

declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface; use Zoosper\Core\Console\ConsoleOutput;
final readonly class VersionCommand implements ConsoleCommandInterface
{
 public function __construct(private string $basePath) {} public function name(): string{return 'version';} public function description(): string{return 'Display the Zoosper release version.';}
 public function run(array $args, ConsoleOutput $output): int { $v=require $this->basePath.'/config/version.php'; $output->writeln('Zoosper '.$v['version'].' ('.$v['channel'].')'); return 0; }
}
