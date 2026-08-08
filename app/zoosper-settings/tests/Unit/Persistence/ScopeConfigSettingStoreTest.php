<?php

declare(strict_types=1);

use Zoosper\Core\Config\Scope\ScopeConfigRepository;
use Zoosper\Core\Config\Scope\ScopeContext;
use Zoosper\Core\Config\Scope\ScopeType;
use Zoosper\Settings\Persistence\ScopeConfigSettingStore;

it('writes resolves and clears scoped values through one Settings boundary', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL, UNIQUE(scope_type, scope_key, config_path))');
    $store = new ScopeConfigSettingStore($pdo, new ScopeConfigRepository($pdo));
    $store->writeMany(['mail.enabled' => '1'], ScopeType::Website, 'main');
    expect($store->resolve('mail.enabled', new ScopeContext(websiteCode: 'main'))['value'])->toBe('1');
    $store->clear('mail.enabled', ScopeType::Website, 'main');
    expect($store->resolve('mail.enabled', new ScopeContext(websiteCode: 'main'))['resolvedScope'])->toBeNull();
});
