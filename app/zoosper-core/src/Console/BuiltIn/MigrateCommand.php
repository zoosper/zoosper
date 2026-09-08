<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Database\Migrator;
final readonly class MigrateCommand implements ConsoleCommandInterface
{
    public function __construct(private Migrator $migrator) {}
    public function name(): string { return 'migrate'; }
    public function description(): string { return 'Apply module-owned database migrations.'; }
    public function run(array $args, ConsoleOutput $output): int
    {
        if ($args !== []) {
            $output->errorln('The migrate command does not accept options and has no dry-run mode. Use a disposable database for upgrade rehearsal and schema:foreign-keys:status for read-only foreign-key inspection.');
            return 2;
        }
        $this->migrator->migrate();
        $output->writeln('Zoosper migrations completed.');
        return 0;
    }
}










