<?php

declare(strict_types=1);

it('captures the existing Page in the persistence closure used for update revisions', function (): void {
    $root = dirname(__DIR__, 4);
    $source = (string) file_get_contents($root . '/src/Application/Save/PageSaveCoordinator.php');
    expect($source)
        ->toContain('use ($action, $input, $page, $user, &$pageId)')
        ->toContain('capturePage($page, $user->id)')
        ->not->toContain('use ($action, $input, $user, &$pageId)');
});










