<?php

declare(strict_types=1);

namespace Zoosper\Editor\Tests\Unit\Config;

use PDO;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Editor\Config\ContentEditorRuntimeConfigFactory;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;

function editorModuleFactoryScopeRepository(): ScopeConfigRepository
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL)');

    return new ScopeConfigRepository($pdo);
}

it('builds scoped editor runtime configuration using explicit site scopes', function (): void {
    $scoped = editorModuleFactoryScopeRepository();
    $scoped->set('editor.default_editor', ScopeType::Site, '2', 'textarea');
    $factory = new ContentEditorRuntimeConfigFactory(
        ConfigRepository::fromArray(['editor' => ['default_editor' => 'editorjs']]),
        $scoped,
    );

    $defaultConfig = $factory->forDefaultScope();
    $siteConfig = $factory->forScope(new ScopeContext(siteId: 2));

    expect($defaultConfig->preferred())->toBe('editorjs')
        ->and($siteConfig->preferred())->toBe('textarea');
});
