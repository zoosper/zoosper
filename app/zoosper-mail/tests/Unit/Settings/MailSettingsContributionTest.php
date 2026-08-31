<?php

declare(strict_types=1);

it('owns grouped value-safe Mail settings metadata', function (): void {
    $root = dirname(__DIR__, 5);
    $sections = require $root . '/app/zoosper-mail/config/admin_settings.php';
    $section = $sections[0];
    $paths = [];
    foreach ($section['groups'] as $group) {
        foreach ($group['settings'] as $setting) {
            $paths[$setting['path']] = $setting;
        }
    }
    expect($section['id'])->toBe('mail.delivery')
        ->and($section['category'])->toBe('communication')
        ->and($section['groups'])->toHaveCount(4)
        ->and(array_keys($paths))->toContain('mail.default','mail.from_address','mail.from_name','mail.smtp.host','mail.smtp.port','mail.smtp.encryption','mail.smtp.timeout_seconds','mail.smtp.username','mail.smtp.password')
        ->and($paths['mail.default']['default'])->toBe('smtp')
        ->and($paths['mail.smtp.password']['secret'])->toBeTrue()
        ->and($paths['mail.smtp.password']['read_only'])->toBeTrue();
});

it('keeps all Mail fields read-only until scoped runtime adoption', function (): void {
    $root = dirname(__DIR__, 5);
    $sections = require $root . '/app/zoosper-mail/config/admin_settings.php';
    foreach ($sections[0]['groups'] as $group) {
        foreach ($group['settings'] as $setting) {
            expect($setting['read_only'] ?? false)->toBeTrue();
        }
    }
});










