<?php

declare(strict_types=1);

use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Catalogue\SettingsCatalogue;
use Zoosper\Settings\Definition\SettingsSection;

it('sorts sections deterministically and searches section and field metadata', function (): void {
    $catalogue = new SettingsCatalogue([
        new SettingsSection('system.mail', 'Email', 'integrations', 'mail', [new SettingDefinition('mail.sender', 'Sender email', 'email')], sortOrder: 20),
        new SettingsSection('system.admin', 'Administration', 'advanced', 'admin', [new SettingDefinition('admin.base_path', 'Admin base path')], sortOrder: 10),
    ]);

    expect(array_map(static fn ($section) => $section->id, $catalogue->all()))
        ->toBe(['system.admin', 'system.mail'])
        ->and($catalogue->search('sender'))->toHaveCount(1)
        ->and($catalogue->search('MAIL'))->toHaveCount(1);
});

it('rejects unsupported field types and unmarked secrets', function (): void {
    expect(fn () => new SettingDefinition('example.invalid', 'Invalid', 'html'))->toThrow(InvalidArgumentException::class)
        ->and(fn () => new SettingDefinition('example.secret', 'Secret', 'secret'))->toThrow(InvalidArgumentException::class);
});










