<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Scaffold\ApiGridScaffolder;
/** @psalm-api */
final readonly class MakeApiGridCommand implements ConsoleCommandInterface
{
    public function __construct(private ApiGridScaffolder $scaffolder) {}
    #[\Override]
    public function name(): string { return 'make:api-grid'; }
    #[\Override]
    public function description(): string { return 'Scaffold a disabled API-backed Grid package.'; }
    #[\Override]
    public function run(array $args, ConsoleOutput $output): int
    {
        $options=ConsoleOptions::parse($args);
        $result=$this->scaffolder->scaffold($args[0]??'', ConsoleOptions::required($options,'key'), ConsoleOptions::required($options,'route'));
        $output->writeln("Created API Grid package {$result->packageName}");
        $output->writeln("Grid key: {$result->gridKey}");
        $output->writeln("Proposed route: {$result->route}");
        $output->writeln('Files:'); foreach($result->createdFiles as $file){$output->writeln("  - {$file}");}
        $output->writeln('Next: implement and test trusted configuration, mapping, permission and presentation before enabling routes.');
        return 0;
    }
}
