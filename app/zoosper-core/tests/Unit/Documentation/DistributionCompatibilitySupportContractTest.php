<?php
declare(strict_types=1);

it('publishes the supported distribution compatibility and support contract', function (): void {
    $root = dirname(__DIR__, 5);
    $policy = (string) file_get_contents($root . '/docs/compatibility-and-support.md');
    $roadmap = (string) file_get_contents($root . '/ROADMAP.md');
    $modules = (string) file_get_contents($root . '/docs/modules.md');
    $upgrade = (string) file_get_contents($root . '/docs/upgrade.md');
    $index = (string) file_get_contents($root . '/docs/README.md');

    expect($policy)
        ->toContain('synchronised monorepo release')
        ->toContain('Individual first-party package publication is not part of the supported distribution contract')
        ->toContain('type `zoosper-module`')
        ->toContain('public compatibility boundaries')
        ->toContain('Deprecations must identify the replacement')
        ->toContain('PHP 8.5 or later')
        ->toContain('MySQL 8.0 or MariaDB 10.6 or later')
        ->toContain('latest immutable prerelease and current `dev`')
        ->and($roadmap)->toContain('- [x] **C3.1 Distribution model.**')
        ->and($roadmap)->toContain('- [x] **C3.2 Compatibility policy.**')
        ->and($roadmap)->toContain('- [x] **C3.3 Upgrade and support policy.**')
        ->and($roadmap)->toContain('- [x] **C4.1 Media queue observability.**')
        ->and($modules)->toContain('[distribution, compatibility and support contract](compatibility-and-support.md)')
        ->and($upgrade)->toContain('[distribution, compatibility and support contract](compatibility-and-support.md)')
        ->and($index)->toContain('[Distribution, compatibility and support](compatibility-and-support.md)');
});
