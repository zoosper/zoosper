<?php
declare(strict_types=1);
namespace Zoosper\Core\Console\BuiltIn;
use JsonException;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Module\ModuleManifestStatus;
final readonly class ManifestCheckCommand implements ConsoleCommandInterface
{
    public function __construct(private ModuleManifestStatus $status) {}
    public function name(): string { return 'module:manifest:check'; }
    public function description(): string { return 'Fail unless the compiled module manifest is fresh.'; }
    /** @throws JsonException */
    public function run(array $args, ConsoleOutput $output): int
    {
        $options = ConsoleOptions::parse($args); $format = $options['format'] ?? 'text';
        if (!in_array($format, ['text', 'json'], true)) { $output->errorln("Unsupported format '{$format}'. Expected text or json."); return 2; }
        $status = $this->status->inspect(); $healthy = $status['status'] === 'fresh';
        if ($format === 'json') { $output->writeln(json_encode(['healthy' => $healthy] + $status, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)); return $healthy ? 0 : 1; }
        if ($healthy) { $output->writeln("Module manifest check passed: fresh ({$status['moduleCount']} module(s))."); return 0; }
        $output->errorln("Module manifest check failed: {$status['status']}.");
        $output->errorln("Cache path: {$status['cachePath']}");
        if ($status['rejectionReason'] !== null) { $output->errorln("Rejection reason: {$status['rejectionReason']}"); }
        $output->errorln('Run `bin/zoosper compile` to generate a fresh manifest.');
        return 1;
    }
}










