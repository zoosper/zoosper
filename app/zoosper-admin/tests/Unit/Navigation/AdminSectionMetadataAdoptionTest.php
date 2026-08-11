<?php

declare(strict_types=1);

use Zoosper\Admin\Navigation\AdminMenuItem;
use Zoosper\Admin\Navigation\AdminSectionBuilder;
use Zoosper\Admin\Navigation\AdminSectionMetadata;
use Zoosper\Admin\Navigation\AdminSectionMetadataLoader;

it('applies configured section order labels and icons independently of item discovery order', function (): void {
    $sections = (new AdminSectionBuilder())->build([
        new AdminMenuItem('settings', 'Settings', '/admin/settings', group: 'System'),
        new AdminMenuItem('pages', 'Pages', '/admin/pages', group: 'Content'),
    ], [
        'content' => new AdminSectionMetadata('content', 'Content', 'content', 10),
        'system' => new AdminSectionMetadata('system', 'System', 'settings', 40),
    ]);
    expect(array_map(static fn ($section): string => $section->getId(), $sections))->toBe(['content', 'system'])
        ->and($sections[0]->getIcon())->toBe('content')
        ->and($sections[1]->getIcon())->toBe('settings');
});

it('keeps undeclared extension groups visible with deterministic fallback metadata', function (): void {
    $sections = (new AdminSectionBuilder())->build([
        new AdminMenuItem('reports', 'Reports', '/admin/reports', group: 'Custom Reports'),
    ]);
    expect($sections)->toHaveCount(1)
        ->and($sections[0]->getId())->toBe('custom-reports')
        ->and($sections[0]->getLabel())->toBe('Custom Reports')
        ->and($sections[0]->getSortOrder())->toBe(1000);
});

it('publishes documented module-owned metadata and explicit service wiring', function (): void {
    $root = dirname(__DIR__, 5);
    $services = (string) file_get_contents($root . '/app/zoosper-admin/config/services.php');
    $sections = require $root . '/app/zoosper-admin/config/admin_sections.php';
    $readme = (string) file_get_contents($root . '/app/zoosper-admin/README.md');
    expect(array_column($sections, 'id'))->toBe(['content', 'design', 'users', 'system', 'main'])
        ->and($services)->toContain('AdminSectionMetadataLoader::class')
        ->toContain('AdminSectionBuilder::class')
        ->toContain('$services->get(AdminSectionBuilder::class)')
        ->and($readme)->toContain('config/admin_sections.php');
});
