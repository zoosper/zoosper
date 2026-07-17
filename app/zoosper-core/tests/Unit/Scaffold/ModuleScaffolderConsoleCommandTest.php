<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Core\Scaffold\ModuleScaffolder;

function consoleScaffoldTempRoot(): string
{
    $root = sys_get_temp_dir() . '/zoosper-console-scaffold-' . bin2hex(random_bytes(6));
    mkdir($root, 0775, true);

    return $root;
}

test('module scaffolder creates a console command config placeholder', function () {
    $root = consoleScaffoldTempRoot();
    $result = (new ModuleScaffolder($root))->scaffold('Acme_Blog');

    $consoleFile = $root . '/' . $result->modulePath . '/config/console.php';

    expect(is_file($consoleFile))->toBeTrue();
    expect((string) file_get_contents($consoleFile))->toContain('ConsoleCommandClass::class');
    expect($result->createdFiles)->toContain($result->modulePath . '/config/console.php');
});

test('generated module readme mentions module-owned console commands', function () {
    $root = consoleScaffoldTempRoot();
    $result = (new ModuleScaffolder($root))->scaffold('Acme_Cli');

    $readme = (string) file_get_contents($root . '/' . $result->modulePath . '/README.md');

    expect($readme)->toContain('config/console.php');
    expect($readme)->toContain('bin/zoosper');
});
