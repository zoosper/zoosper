<?php

declare(strict_types=1);

use Zoosper\Admin\Navigation\AdminMenuItem;
use Zoosper\Admin\Navigation\AdminNavigationRenderer;
use Zoosper\Admin\Navigation\AdminSection;

it('renders destination icons while keeping section headings text-only and non-interactive', function (): void {
    $html = (new AdminNavigationRenderer())->render([
        new AdminSection('content', 'Content', [
            new AdminMenuItem('dashboard', 'Dashboard', '/admin', icon: 'dashboard'),
        ], icon: 'content'),
        new AdminSection('users', 'Users', [
            new AdminMenuItem('admin-users', 'Admin Users', '/admin/users', icon: 'users'),
        ], icon: 'users'),
    ], 'dashboard', '');

    expect($html)
        ->toContain('<h2 class="menu-group"><span>Content</span></h2>')
        ->toContain('<h2 class="menu-group"><span>Users</span></h2>')
        ->toContain('data-admin-icon="dashboard"')
        ->toContain('data-admin-icon="users"')
        ->toContain('<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">')
        ->toContain('<rect x="3" y="3" width="7" height="7" rx="1"/>')
        ->not->toContain('data-admin-icon="content"')
        ->not->toContain('<h2 class="menu-group"><a')
        ->not->toContain('<h2 class="menu-group"><button')
        ->not->toContain('tabindex=');
});

it('escapes destination icon identifiers exactly once as attribute metadata', function (): void {
    $html = (new AdminNavigationRenderer())->render([
        new AdminSection('unsafe', 'Safe label', [
            new AdminMenuItem('item', 'Safe item', '/admin/item', icon: '"><svg>'),
        ], icon: '<script>'),
    ], '', '');

    expect($html)
        ->toContain('<h2 class="menu-group"><span>Safe label</span></h2>')
        ->toContain('data-admin-icon="&quot;&gt;&lt;svg&gt;"')
        ->toContain('<circle cx="12" cy="12" r="8"/>')
        ->not->toContain('data-admin-icon="&lt;script&gt;"')
        ->not->toContain('<script>')
        ->not->toContain('<svg>');
});

it('gives every discovered Admin destination a module-owned icon identifier', function (): void {
    $root = dirname(__DIR__, 5);
    $files = array_merge(
        glob($root . '/app/*/config/admin_menu.php') ?: [],
        glob($root . '/packages/*/config/admin_menu.php') ?: [],
    );
    sort($files);

    expect($files)->toHaveCount(10);

    $renderer = new AdminNavigationRenderer();
    foreach ($files as $file) {
        $items = require $file;
        expect($items)->toBeArray();

        foreach ($items as $item) {
            $identifier = trim((string) ($item['icon'] ?? ''));
            expect($identifier)->not->toBe('');
            expect($renderer->renderIcon($identifier))
                ->toContain('data-admin-icon="' . htmlspecialchars($identifier, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '"')
                ->toContain('<svg viewBox="0 0 24 24"');
        }
    }
});

it('renders a neutral safe fallback for empty and unknown identifiers', function (): void {
    $renderer = new AdminNavigationRenderer();

    expect($renderer->renderIcon(''))
        ->toContain('data-admin-icon="fallback"')
        ->toContain('<circle cx="12" cy="12" r="8"/>')
        ->and($renderer->renderIcon('third-party-icon'))
        ->toContain('data-admin-icon="third-party-icon"')
        ->toContain('<circle cx="12" cy="12" r="8"/>');
});
