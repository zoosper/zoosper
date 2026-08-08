<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use JsonException;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Module\ModuleManifestStatus;
final readonly class ManifestStatusCommand implements ConsoleCommandInterface
{
    public function __construct(private ModuleManifestStatus $status) {}
    public function name(): string { return 'module:manifest:status'; }
    public function description(): string { return 'Inspect compiled module-manifest health.'; }
    /** @throws JsonException */
    public function run(array $args, ConsoleOutput $output): int
    {
        $options = ConsoleOptions::parse($args); $format = $options['format'] ?? 'text';
        if (!in_array($format, ['text', 'json'], true)) { $output->errorln("Unsupported format '{$format}'. Expected text or json."); return 2; }
        $status = $this->status->inspect();
        if ($format === 'json') { $output->writeln(json_encode($status, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)); return 0; }
        $output->writeln("Module manifest status: {$status['status']}");
        $output->writeln("Cache path: {$status['cachePath']}");
        $output->writeln('Enabled modules: ' . $status['moduleCount']);
        if ($status['rejectionReason'] !== null) { $output->writeln("Rejection reason: {$status['rejectionReason']}"); }
        return 0;
    }
}
