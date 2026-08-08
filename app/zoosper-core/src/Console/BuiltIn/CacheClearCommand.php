<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Module\ModuleManifestCompiler;
final readonly class CacheClearCommand implements ConsoleCommandInterface
{
    public function __construct(private ModuleManifestCompiler $compiler) {}
    public function name(): string { return 'cache:clear'; }
    public function description(): string { return 'Clear the compiled module manifest.'; }
    public function run(array $args, ConsoleOutput $output): int
    {
        $compiled = $this->compiler->isCompiled(); $this->compiler->clear();
        $output->writeln($compiled ? 'Compiled module manifest cache cleared.' : 'No compiled module manifest cache was present (already using live discovery).');
        return 0;
    }
}
