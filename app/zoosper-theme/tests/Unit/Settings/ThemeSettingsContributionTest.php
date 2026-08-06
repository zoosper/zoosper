<?php

declare(strict_types=1);

it('owns grouped project-controlled Theme settings metadata', function (): void {
    $root = dirname(__DIR__, 5);
    $sections = require $root . '/app/zoosper-theme/config/admin_settings.php';
    $section = $sections[0];
    $paths = [];
    foreach ($section['groups'] as $group) {
        foreach ($group['settings'] as $setting) {
            $paths[$setting['path']] = $setting;
        }
    }
    expect($section['id'])->toBe('theme.rendering')
        ->and($section['category'])->toBe('design')
        ->and($section['groups'])->toHaveCount(2)
        ->and(array_keys($paths))->toBe(['template.engine', 'template.template_cache_path'])
        ->and($paths['template.engine']['options'])->toBe(['latte', 'php']);
    foreach ($paths as $setting) {
        expect($setting['read_only'])->toBeTrue();
    }
});
