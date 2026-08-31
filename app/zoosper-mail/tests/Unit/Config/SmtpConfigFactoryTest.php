<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;
use Zoosper\Mail\Config\SmtpConfigFactory;

it('creates immutable Mail configuration for explicit and Default scopes', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL)');
    $scoped = new ScopeConfigRepository($pdo);
    $scoped->set('mail.from_name', ScopeType::Default, null, 'Default Sender');
    $scoped->set('mail.from_name', ScopeType::Site, '42', 'Site Sender');
    $factory = new SmtpConfigFactory(ConfigRepository::fromArray([]), $scoped);

    expect($factory->forDefaultScope()->fromName())->toBe('Default Sender')
        ->and($factory->forScope(new ScopeContext(siteId: 42))->fromName())->toBe('Site Sender');
});










