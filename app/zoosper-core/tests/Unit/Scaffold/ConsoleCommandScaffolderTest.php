<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Scaffold;

use Zoosper\Core\Scaffold\ConsoleCommandScaffolder;
use Zoosper\Core\Scaffold\ModuleScaffolder;

function consoleCommandScaffoldTempRoot(): string
{
    $root = sys_get_temp_dir() . '/zoosper-command-scaffold-' . bin2hex(random_bytes(6));
    mkdir($root, 0775, true);

    return $root;
}

test('scaffolds a module-owned console command and wires console config', function () {
    $root = consoleCommandScaffoldTempRoot();
    (new ModuleScaffolder($root))->scaffold('Acme_Blog');

    $result = (new ConsoleCommandScaffolder($root))->scaffold('Acme_Blog', 'ReindexPostsCommand', 'blog:posts:reindex', 'Reindex blog posts.');

    expect($result->commandName)->toBe('blog:posts:reindex');
    expect(is_file($root . '/app/acme-blog/src/Console/ReindexPostsCommand.php'))->toBeTrue();
    expect((string) file_get_contents($root . '/app/acme-blog/src/Console/ReindexPostsCommand.php'))->toContain('ConsoleCommandInterface');
    expect((string) file_get_contents($root . '/app/acme-blog/config/console.php'))->toContain('ReindexPostsCommand::class');
});

test('rejects invalid console command names', function () {
    $root = consoleCommandScaffoldTempRoot();
    (new ModuleScaffolder($root))->scaffold('Acme_Blog');

    (new ConsoleCommandScaffolder($root))->scaffold('Acme_Blog', 'ReindexPostsCommand', 'not-valid', 'Bad.');
})->throws(\Zoosper\Core\Exception\ZoosperException::class, 'Invalid console command name');
