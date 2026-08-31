<?php

declare(strict_types=1);

it('wires complete Page revision capture history preview and restore', function (): void {
    $root = dirname(__DIR__, 5);
    $repository = (string) file_get_contents($root . '/app/zoosper-page/src/Repository/PageRepository.php');
    $save = (string) file_get_contents($root . '/app/zoosper-page/src/Application/Save/PageSaveCoordinator.php');
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $routes = (string) file_get_contents($root . '/app/zoosper-page/config/admin_routes.php');
    expect($repository)->toContain('restoreRevision(')->not->toContain('private function createRevision(')
        ->and($save)->toContain('capturePage($page, $user->id)')
        ->and($controller)->toContain('PageRevisionAdminResponder')->toContain('restoreRevision(')
        ->and($routes)->toContain('/revisions/{revisionId:\d+}/preview')->toContain('/revisions/{revisionId:\d+}/restore');
});










