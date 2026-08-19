<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;
use Zoosper\Mail\Config\SmtpConfig;

function mailScopeRepository(): ScopeConfigRepository
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL)');

    return new ScopeConfigRepository($pdo);
}

it('preserves project configuration when scoped runtime storage is not supplied', function (): void {
    $config = new SmtpConfig(ConfigRepository::fromArray(['mail' => ['smtp' => ['host' => 'project.test', 'port' => 2525]]]));

    expect($config->host())->toBe('project.test')
        ->and($config->port())->toBe(2525)
        ->and($config->transport())->toBe('smtp');
});

it('lets Default-scope database values override project values for all runtime types', function (): void {
    $scoped = mailScopeRepository();
    $scoped->set('mail.smtp.host', ScopeType::Default, null, 'database.test');
    $scoped->set('mail.smtp.port', ScopeType::Default, null, '587');
    $scoped->set('mail.smtp.encryption', ScopeType::Default, null, 'TLS');
    $scoped->set('mail.smtp.timeout_seconds', ScopeType::Default, null, '30');
    $scoped->set('mail.smtp.password', ScopeType::Default, null, 'runtime-secret');
    $config = new SmtpConfig(ConfigRepository::fromArray(['mail' => ['smtp' => ['host' => 'project.test']]]), $scoped, ScopeContext::default());

    expect($config->host())->toBe('database.test')
        ->and($config->port())->toBe(587)
        ->and($config->encryption())->toBe('tls')
        ->and($config->timeoutSeconds())->toBe(30)
        ->and($config->password())->toBe('runtime-secret');
});

it('uses the supplied scope context with site store website and Default inheritance', function (): void {
    $scoped = mailScopeRepository();
    $scoped->set('mail.from_name', ScopeType::Default, null, 'Default Sender');
    $scoped->set('mail.from_name', ScopeType::Website, 'main', 'Website Sender');
    $scoped->set('mail.from_name', ScopeType::Store, 'main_store', 'Store Sender');
    $scoped->set('mail.from_name', ScopeType::Site, '42', 'Site Sender');
    $config = new SmtpConfig(ConfigRepository::fromArray([]), $scoped, new ScopeContext(42, 'main_store', 'main'));

    expect($config->fromName())->toBe('Site Sender');
});
