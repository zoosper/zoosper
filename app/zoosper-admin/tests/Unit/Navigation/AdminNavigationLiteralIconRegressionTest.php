<?php

declare(strict_types=1);

use Zoosper\Admin\Navigation\AdminMenuItem;
use Zoosper\Admin\Navigation\AdminNavigationRenderer;
use Zoosper\Admin\Navigation\AdminSection;

it('keeps Marko icon identifiers as inert metadata instead of visible labels', function (): void {
    $html = (new AdminNavigationRenderer())->render([
        new AdminSection('content', 'Content', [
            new AdminMenuItem('dashboard', 'Dashboard', '/admin', icon: 'dashboard'),
        ], icon: 'content'),
        new AdminSection('system', 'System', [
            new AdminMenuItem('settings', 'Settings', '/admin/settings', icon: 'settings'),
        ], icon: 'settings'),
    ], 'dashboard', '');

    expect($html)
        ->toContain('data-admin-icon="content"')
        ->toContain('data-admin-icon="dashboard"')
        ->toContain('data-admin-icon="settings"')
        ->not->toContain('>content</span>')
        ->not->toContain('>dashboard</span>')
        ->not->toContain('>settings</span>')
        ->not->toContain('contentContent')
        ->not->toContain('settingsSystem');
});

it('escapes icon identifiers exactly once as attribute metadata', function (): void {
    $html = (new AdminNavigationRenderer())->render([
        new AdminSection('unsafe', 'Safe label', [
            new AdminMenuItem('item', 'Safe item', '/admin/item', icon: '"><svg>'),
        ], icon: '<script>'),
    ], '', '');

    expect($html)
        ->toContain('data-admin-icon="&lt;script&gt;"')
        ->toContain('data-admin-icon="&quot;&gt;&lt;svg&gt;"')
        ->not->toContain('<script>')
        ->not->toContain('<svg>');
});
