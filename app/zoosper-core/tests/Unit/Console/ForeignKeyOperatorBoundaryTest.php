<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Console;

use PDO;
use Zoosper\Core\Console\BuiltIn\SchemaForeignKeyApplyCommand;
use Zoosper\Core\Console\BuiltIn\SchemaForeignKeyStatusCommand;
use Zoosper\Core\Console\ConsoleOutput;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Database\Schema\SchemaForeignKeyReconciliationService;
use Zoosper\Database\Schema\SchemaLoader;

function fkOperatorOutput(): array
{
    $out=fopen('php://memory','w+'); $err=fopen('php://memory','w+');
    return [new ConsoleOutput($out,$err),$out,$err];
}
function fkOperatorRead($stream): string { rewind($stream); return (string) stream_get_contents($stream); }
function fkOperatorService(): SchemaForeignKeyReconciliationService
{
    $pdo=new PDO('sqlite::memory:'); $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    return new SchemaForeignKeyReconciliationService($pdo,'sqlite',new SchemaLoader(new ModuleRegistry(sys_get_temp_dir().'/zoosper-no-modules-' . bin2hex(random_bytes(4)))));
}

it('publishes text and json status with stable empty-registry exit semantics', function (): void {
    $command=new SchemaForeignKeyStatusCommand(fkOperatorService());
    [$output,$stdout]=$a=fkOperatorOutput();
    expect($command->run([], $output))->toBe(0)->and(fkOperatorRead($stdout))->toContain('No declarative foreign keys');
    [$jsonOutput,$jsonStdout]=fkOperatorOutput();
    expect($command->run(['--format=json'],$jsonOutput))->toBe(0);
    $payload=json_decode(fkOperatorRead($jsonStdout),true,512,JSON_THROW_ON_ERROR);
    expect($payload['blocked'])->toBeFalse()->and($payload['constraints'])->toBe([]);
});

it('rejects invalid formats and apply without explicit confirmation', function (): void {
    $service=fkOperatorService(); $status=new SchemaForeignKeyStatusCommand($service);
    [$badOutput,,$badErr]=fkOperatorOutput();
    expect($status->run(['--format=xml'],$badOutput))->toBe(2)->and(fkOperatorRead($badErr))->toContain('Expected text or json');
    [$applyOutput,,$applyErr]=fkOperatorOutput();
    expect((new SchemaForeignKeyApplyCommand($service,$status))->run([], $applyOutput))->toBe(2)
        ->and(fkOperatorRead($applyErr))->toContain('--confirm=apply');
});

it('keeps dry-run read-only and routes it through status output', function (): void {
    $service=fkOperatorService(); $status=new SchemaForeignKeyStatusCommand($service);
    [$output,$stdout]=fkOperatorOutput();
    expect((new SchemaForeignKeyApplyCommand($service,$status))->run(['--dry-run=1'],$output))->toBe(0)
        ->and(fkOperatorRead($stdout))->toContain('Foreign-key reconciliation status');
});

it('wires both operational commands lazily in the thin executable', function (): void {
    $root=dirname(__DIR__,5); $source=(string) file_get_contents($root.'/bin/zoosper');
    expect($source)->toContain("'schema:foreign-keys:status' => static function")
        ->toContain("'schema:foreign-keys:apply' => static function")
        ->toContain('new SchemaForeignKeyReconciliationService(')
        ->toContain('$connection->get()');
});










