<?php

declare(strict_types=1);

use Zoosper\Theme\Theme\ProjectRootLocator;

it('locates the owning project from source and vendor module layouts', function (string $modulePath): void {
    $root = sys_get_temp_dir() . '/zoosper-theme-root-' . bin2hex(random_bytes(8));
    try {
        mkdir($root . '/' . $modulePath . '/config', 0700, true);
        file_put_contents($root . '/composer.json', '{"name":"zoosper/zoosper"}');

        expect(ProjectRootLocator::from($root . '/' . $modulePath . '/config'))->toBe($root);
    } finally {
        if (is_dir($root)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $entry) {
                $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
            }
            rmdir($root);
        }
    }
})->with(['app/zoosper-theme', 'vendor/zoosper/theme']);

it('fails closed when no owning project exists', function (): void {
    $root = sys_get_temp_dir() . '/zoosper-theme-root-' . bin2hex(random_bytes(8));
    try {
        mkdir($root . '/vendor/zoosper/theme/config', 0700, true);
        expect(fn (): string => ProjectRootLocator::from($root . '/vendor/zoosper/theme/config'))
            ->toThrow(RuntimeException::class, 'Theme runtime cannot locate the zoosper/zoosper project root.');
    } finally {
        if (is_dir($root)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $entry) {
                $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
            }
            rmdir($root);
        }
    }
});

it('wires project-owned themes without module-relative dirname arithmetic', function (): void {
    $root = dirname(__DIR__, 5);
    $services = (string) file_get_contents($root . '/app/zoosper-theme/config/services.php');
    $page = (string) file_get_contents($root . '/app/zoosper-page/src/Service/PageRenderer.php');
    $admin = (string) file_get_contents($root . '/app/zoosper-admin/src/Layout/AdminLayout.php');

    expect($services)
        ->toContain('ProjectRootLocator::from(__DIR__)')
        ->not->toContain("dirname(__DIR__, 3) . '/themes");
    expect($page)->toContain("ProjectRootLocator::from(__DIR__) . '/themes'");
    expect($admin)->toContain("ProjectRootLocator::from(__DIR__) . '/themes/admin'");
});
