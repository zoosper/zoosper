<?php

declare(strict_types=1);

use Zoosper\Settings\Admin\SettingsPresentationBuilder;
use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Definition\SettingsGroup;
use Zoosper\Settings\Definition\SettingsSection;
use Zoosper\Settings\Value\SettingValue;

it('prepares ordered category module section and field presentation metadata', function (): void {
    $setting = new SettingDefinition('mail.enabled', 'Enabled', 'boolean', 'Mail switch');
    $section = new SettingsSection('mail.general', 'Mail', 'communication', 'zoosper-mail', [$setting], groups: [new SettingsGroup('general', 'General', [$setting])]);
    $data = (new SettingsPresentationBuilder())->build([$section], [
        'mail.enabled' => new SettingValue('mail.enabled', true, 'database', false),
    ], ['main'], ['default'], []);

    expect(array_keys($data['categories']))->toBe(['communication'])
        ->and($data['settingsModules'])->toBe(['zoosper-mail' => 'zoosper-mail'])
        ->and($data['sectionPresentation']['mail.general']['editable'])->toBeTrue()
        ->and($data['fieldPresentation']['mail.enabled']['id'])->toBe('setting-mail-enabled')
        ->and($data['fieldPresentation']['mail.enabled']['checked'])->toBeTrue()
        ->and($data['fieldPresentation']['mail.enabled']['displayValue'])->toBe('Enabled');
});

it('redacts secrets and prepares multiselect and numeric input metadata', function (): void {
    $builder = new SettingsPresentationBuilder();
    $secret = new SettingDefinition('api.secret', 'Secret', 'secret', secret: true);
    $multi = new SettingDefinition('ui.options', 'Options', 'multiselect', options: ['a', 'b']);
    $decimal = new SettingDefinition('tax.rate', 'Rate', 'decimal');
    $section = new SettingsSection('system.fields', 'Fields', 'advanced', 'settings', [$secret, $multi, $decimal], groups: [new SettingsGroup('fields', 'Fields', [$secret, $multi, $decimal])]);
    $data = $builder->build([$section], [
        'api.secret' => new SettingValue('api.secret', null, 'project', true, true),
        'ui.options' => new SettingValue('ui.options', '["a"]', 'database', false),
        'tax.rate' => new SettingValue('tax.rate', '10.5', 'database', false),
    ], [], [], []);

    expect($data['fieldPresentation']['api.secret']['displayValue'])->toBe('••••••••')
        ->and($data['fieldPresentation']['ui.options']['selectedValues'])->toBe(['a'])
        ->and($data['fieldPresentation']['tax.rate']['inputType'])->toBe('number')
        ->and($data['fieldPresentation']['tax.rate']['step'])->toBe('any');
});
