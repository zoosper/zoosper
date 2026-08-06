<?php

declare(strict_types=1);

use Zoosper\Admin\Editor\Config\ContentEditorRuntimeConfigFactory;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Config\Scope\ScopeConfigRepository;
use Zoosper\Core\Config\Scope\ScopeContext;
use Zoosper\Core\Config\Scope\ScopeType;

it('creates editor configuration for Default and explicit scopes', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL)');
    $scoped = new ScopeConfigRepository($pdo);
    $scoped->set('editor.default_editor', ScopeType::Default, null, 'editorjs');
    $scoped->set('editor.default_editor', ScopeType::Site, '42', 'textarea');

    $factory = new ContentEditorRuntimeConfigFactory(
        ConfigRepository::fromArray(['editor' => ['fallback_editor' => 'textarea']]),
        $scoped,
    );

    expect($factory->forDefaultScope()->preferred())->toBe('editorjs')
        ->and($factory->forScope(new ScopeContext(siteId: 42))->preferred())->toBe('textarea')
        ->and($factory->forScope(new ScopeContext(siteId: 42))->fallback())->toBe('textarea');
});
