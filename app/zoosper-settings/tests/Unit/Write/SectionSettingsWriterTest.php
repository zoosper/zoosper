<?php

declare(strict_types=1);

use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\Settings\Persistence\ScopeConfigSettingStore;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;
use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Definition\SettingsSection;
use Zoosper\Settings\Write\SectionSettingsWriter;
use Zoosper\Settings\Write\SettingValidationException;
use Zoosper\Settings\Write\SettingValueNormaliser;

function sectionWriterFixture(): array
{
    $pdo = new \PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL, UNIQUE(scope_type, scope_key, config_path))');
    $repository = new ScopeConfigRepository($pdo);
    return [new SectionSettingsWriter(new ScopeConfigSettingStore($pdo, $repository), new SettingValueNormaliser()), $repository];
}

it('writes only declared editable settings at the requested scope', function (): void {
    [$writer, $repository] = sectionWriterFixture();
    $section = new SettingsSection('mail.general', 'Mail', 'email', 'mail', [
        new SettingDefinition('mail.enabled', 'Enabled', 'boolean'),
        new SettingDefinition('mail.sender', 'Sender', 'email'),
        new SettingDefinition('mail.secret', 'Secret', 'secret', secret: true),
    ]);
    $writer->write($section, ScopeType::Website, 'main', ['mail.enabled' => 'on', 'mail.sender' => 'a@example.test', 'unknown.path' => 'ignored']);
    $context = new ScopeContext(websiteCode: 'main');
    expect($repository->get('mail.enabled', $context))->toBe('1')
        ->and($repository->get('mail.sender', $context))->toBe('a@example.test')
        ->and($repository->get('unknown.path', $context))->toBeNull()
        ->and($repository->get('mail.secret', $context))->toBeNull();
});

it('performs no writes when any editable field is invalid', function (): void {
    [$writer, $repository] = sectionWriterFixture();
    $section = new SettingsSection('mail.general', 'Mail', 'email', 'mail', [
        new SettingDefinition('mail.enabled', 'Enabled', 'boolean'),
        new SettingDefinition('mail.sender', 'Sender', 'email'),
    ]);
    expect(fn () => $writer->write($section, ScopeType::Default, null, ['mail.enabled' => 'on', 'mail.sender' => 'invalid']))->toThrow(SettingValidationException::class);
    expect($repository->get('mail.enabled', ScopeContext::default()))->toBeNull();
});

it('writes named Settings values captured through the real globals boundary', function (): void {
    $server = $_SERVER;
    $post = $_POST;
    $files = $_FILES;
    try {
        $_SERVER['REQUEST_URI'] = '/admin/settings/save';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['settings' => ['mail.enabled' => '0', 'mail.sender' => 'a@example.test']];
        $_FILES = [];
        $request = \Zoosper\Core\Http\Request::fromGlobals();
        [$writer, $repository] = sectionWriterFixture();
        $section = new SettingsSection('mail.general', 'Mail', 'email', 'mail', [
            new SettingDefinition('mail.enabled', 'Enabled', 'boolean'),
            new SettingDefinition('mail.sender', 'Sender', 'email'),
        ]);
        $writer->write($section, ScopeType::Website, 'main', $request->form()['settings']);
        $context = new ScopeContext(websiteCode: 'main');
        expect($repository->get('mail.enabled', $context))->toBe('0')
            ->and($repository->get('mail.sender', $context))->toBe('a@example.test');
    } finally {
        $_SERVER = $server;
        $_POST = $post;
        $_FILES = $files;
    }
});
