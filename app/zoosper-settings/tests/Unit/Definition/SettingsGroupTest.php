<?php

declare(strict_types=1);

use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Definition\SettingsGroup;
use Zoosper\Settings\Definition\SettingsSection;

it('keeps organised groups and a flattened compatibility view', function (): void {
    $field = new SettingDefinition('sample.enabled', 'Enabled', 'boolean');
    $group = new SettingsGroup('general', 'General', [$field], openByDefault: true);
    $section = new SettingsSection('sample.general', 'Sample', 'general', 'sample', [$field], groups: [$group]);

    expect($section->groups)->toBe([$group])
        ->and($section->settings)->toBe([$field])
        ->and($section->groups[0]->openByDefault)->toBeTrue();
});

it('rejects drift between grouped and flattened settings', function (): void {
    $grouped = new SettingDefinition('sample.grouped', 'Grouped');
    $flat = new SettingDefinition('sample.flat', 'Flat');
    expect(fn () => new SettingsSection('sample.general', 'Sample', 'general', 'sample', [$flat], groups: [new SettingsGroup('general', 'General', [$grouped])]))
        ->toThrow(\InvalidArgumentException::class);
});










