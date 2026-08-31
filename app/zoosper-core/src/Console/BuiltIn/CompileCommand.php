<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Module\ModuleManifestCompiler;
final readonly class CompileCommand implements ConsoleCommandInterface
{
    public function __construct(private ModuleManifestCompiler $compiler) {}
    public function name(): string { return 'compile'; }
    public function description(): string { return 'Compile the enabled-module manifest cache.'; }
    public function run(array $args, ConsoleOutput $output): int
    {
        $modules = $this->compiler->compile();
        $output->writeln('Compiled module manifest: ' . count($modules) . ' module(s).');
        foreach ($modules as $module) { $output->writeln("  - {$module->name} ({$module->source})"); }
        $output->writeln();
        $output->writeln('Cache written to: var/cache/modules.php');
        $output->writeln('Run `bin/zoosper cache:clear` at any time to force live discovery again.');
        return 0;
    }
}










