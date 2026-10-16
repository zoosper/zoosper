<?php

declare(strict_types=1);

it('locks the post-entrypoint residual clean-up', function (): void {
    $root = dirname(__DIR__, 5);
    $role = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Controller/RoleAdminController.php');
    $roleConfig = (string) file_get_contents($root . '/app/zoosper-auth/config/controllers.php');
    $domains = (string) file_get_contents($root . '/app/zoosper-site/src/Admin/Controller/SiteDomainAdminController.php');
    $factory = (string) file_get_contents($root . '/packages/zoosper-admin-grid/src/GridWorkspaceExportAuditorFactory.php');
    $queue = (string) file_get_contents($root . '/packages/zoosper-media/src/Console/ProcessMediaQueueCommand.php');

    expect($role)->not->toContain('private ?AdminViewRendererInterface $views')
        ->not->toContain('private function e(')
        ->and($roleConfig)->not->toContain('views: $services->has(AdminViewRendererInterface::class)')
        ->and($domains)->not->toContain('private function siteNames(')
        ->and($factory)->toContain('@psalm-suppress UnusedConstructor Static-only factory guard.')
        ->and($queue)->toContain('private function recoverStaleClaims(string $now): void')
        ->not->toContain('return $statement->rowCount();');
});
