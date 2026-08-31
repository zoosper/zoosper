<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Database\Migrator;
use Zoosper\Core\Module\ModuleManifestCompiler;
use Zoosper\Core\Module\ModuleManifestStatus;
final readonly class DeployCommand implements ConsoleCommandInterface
{
    public function __construct(private Migrator $migrator, private ModuleManifestCompiler $compiler, private ModuleManifestStatus $status) {}
    public function name(): string { return 'deploy'; }
    public function description(): string { return 'Regenerate autoloading, migrate and compile the module manifest.'; }
    public function run(array $args, ConsoleOutput $output): int
    {
        $output->writeln('== Zoosper deploy =='); $output->writeln();
        $output->writeln('[1/3] Regenerating Composer autoloader (composer dump-autoload)...');
        passthru('composer dump-autoload', $code);
        if ($code !== 0) { $output->errorln("Autoload generation failed (exit code {$code}). Aborting deploy before any schema changes."); return 1; }
        $output->writeln('[2/3] Applying database migrations (bin/zoosper migrate)...'); $this->migrator->migrate(); $output->writeln('Migrations applied.');
        $output->writeln('[3/3] Compiling module manifest (bin/zoosper compile)...'); $modules = $this->compiler->compile(); $output->writeln('Compiled module manifest: ' . count($modules) . ' module(s).');
        $status = $this->status->inspect();
        if ($status['status'] !== 'fresh') { $output->errorln('Compiled module manifest failed post-compile verification: ' . $status['status']); return 1; }
        $output->writeln('Module manifest post-compile verification passed.'); $output->writeln('== Deploy complete ==');
        $output->writeln('Reminder: this does NOT run your test suite. Run `composer test` before considering this build done.');
        return 0;
    }
}










