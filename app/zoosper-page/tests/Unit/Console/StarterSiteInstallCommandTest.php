<?php

declare(strict_types=1);

it('declares a compact idempotent starter-site command without overwriting content', function (): void {
    $root = dirname(__DIR__, 5);
    $command = (string) file_get_contents($root . '/app/zoosper-page/src/Console/StarterSiteInstallCommand.php');
    $console = (string) file_get_contents($root . '/app/zoosper-page/config/console.php');
    $services = (string) file_get_contents($root . '/app/zoosper-page/config/services.php');

    expect($command)
        ->toContain("return 'starter:install';")
        ->toContain("findByCode(\$siteCode)")
        ->toContain("findPublishedBySlug(\$site->id, \$slug)")
        ->toContain('Retained existing Site')
        ->toContain('Retained existing published Page')
        ->not->toContain('UPDATE pages')
        ->not->toContain('DELETE FROM')
        ->and($console)->toContain('StarterSiteInstallCommand::class')
        ->and($services)->toContain('StarterSiteInstallCommand::class');
});
