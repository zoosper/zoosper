<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Database\Migrator;
final readonly class MigrateCommand implements ConsoleCommandInterface
{
    public function __construct(private Migrator $migrator) {}
    public function name(): string { return 'migrate'; }
    public function description(): string { return 'Apply module-owned database migrations.'; }
    public function run(array $args, ConsoleOutput $output): int
    {
        $this->migrator->migrate();
        $output->writeln('Zoosper migrations completed.');
        return 0;
    }
}
