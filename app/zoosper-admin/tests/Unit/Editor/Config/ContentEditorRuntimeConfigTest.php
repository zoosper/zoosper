<?php

declare(strict_types=1);

use Zoosper\Admin\Editor\Config\ContentEditorRuntimeConfig;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;

function editorRuntimeScopeRepository(): ScopeConfigRepository
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL)');

    return new ScopeConfigRepository($pdo);
}

it('preserves project editor choices including third-party editor codes', function (): void {
    $config = new ContentEditorRuntimeConfig(ConfigRepository::fromArray([
        'editor' => ['default_editor' => 'custom-blocks', 'fallback_editor' => 'textarea'],
    ]));

    expect($config->preferred())->toBe('custom-blocks')
        ->and($config->fallback())->toBe('textarea');
});

it('uses Default-scope database values before project editor choices', function (): void {
    $scoped = editorRuntimeScopeRepository();
    $scoped->set('editor.default_editor', ScopeType::Default, null, 'textarea');
    $scoped->set('editor.fallback_editor', ScopeType::Default, null, 'editorjs');
    $config = new ContentEditorRuntimeConfig(
        ConfigRepository::fromArray(['editor' => ['default_editor' => 'editorjs', 'fallback_editor' => 'textarea']]),
        $scoped,
        ScopeContext::default(),
    );

    expect($config->preferred())->toBe('textarea')
        ->and($config->fallback())->toBe('editorjs');
});

it('falls back from empty values without restricting custom registered codes', function (): void {
    $config = new ContentEditorRuntimeConfig(ConfigRepository::fromArray([
        'editor' => ['default_editor' => '', 'fallback_editor' => ''],
    ]));

    expect($config->preferred())->toBe('editorjs')
        ->and($config->fallback())->toBe('textarea');
});










