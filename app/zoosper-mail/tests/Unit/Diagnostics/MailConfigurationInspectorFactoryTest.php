<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;
use Zoosper\Mail\Config\SmtpConfigFactory;
use Zoosper\Mail\Diagnostics\MailConfigurationInspectorFactory;

it('creates scope-aware redacted diagnostics without exposing password plaintext', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL)');
    $scoped = new ScopeConfigRepository($pdo);
    $scoped->set('mail.smtp.password', ScopeType::Site, '42', 'site-secret');
    $factory = new MailConfigurationInspectorFactory(new SmtpConfigFactory(ConfigRepository::fromArray([]), $scoped));
    $summary = $factory->forScope(new ScopeContext(siteId: 42))->summary();

    expect($summary->passwordConfigured)->toBeTrue()
        ->and(json_encode($summary, JSON_THROW_ON_ERROR))->not->toContain('site-secret');
});
