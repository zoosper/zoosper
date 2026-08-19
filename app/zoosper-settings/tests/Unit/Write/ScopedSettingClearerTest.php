<?php

declare(strict_types=1);

use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\Settings\Persistence\ScopeConfigSettingStore;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;
use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Definition\SettingsSection;
use Zoosper\Settings\Write\ScopedSettingClearer;
use Zoosper\Settings\Write\SettingValidationException;

function clearerFixture(): array
{
    $pdo = new \PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL, UNIQUE(scope_type, scope_key, config_path))');
    $repository = new ScopeConfigRepository($pdo);
    return [new ScopedSettingClearer(new ScopeConfigSettingStore($pdo, $repository)), $repository];
}

it('clears a declared editable override so inheritance resumes', function (): void {
    [$clearer, $repository] = clearerFixture();
    $section = new SettingsSection('settings.catalogue', 'Catalogue', 'advanced', 'settings', [new SettingDefinition('settings.catalogue.show_paths', 'Show paths', 'boolean')]);
    $repository->set('settings.catalogue.show_paths', ScopeType::Default, null, '1');
    $repository->set('settings.catalogue.show_paths', ScopeType::Website, 'main', '0');
    $clearer->clear($section, 'settings.catalogue.show_paths', ScopeType::Website, 'main');
    expect($repository->get('settings.catalogue.show_paths', new ScopeContext(websiteCode: 'main')))->toBe('1');
});

it('rejects undeclared and locked paths', function (): void {
    [$clearer] = clearerFixture();
    $section = new SettingsSection('settings.catalogue', 'Catalogue', 'advanced', 'settings', [new SettingDefinition('settings.locked', 'Locked', readOnly: true)]);
    expect(fn () => $clearer->clear($section, 'unknown.path', ScopeType::Default, null))->toThrow(\InvalidArgumentException::class)
        ->and(fn () => $clearer->clear($section, 'settings.locked', ScopeType::Default, null))->toThrow(SettingValidationException::class);
});
