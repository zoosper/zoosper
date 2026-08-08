<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Config\Scope\ScopeConfigRepository;
use Zoosper\Settings\Persistence\ScopeConfigSettingStore;
use Zoosper\Core\Config\Scope\ScopeContext;
use Zoosper\Core\Config\Scope\ScopeType;
use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Value\ScopedSettingValueResolver;
use Zoosper\Settings\Value\SettingValueResolver;

function scopeResolver(): array
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL, UNIQUE(scope_type, scope_key, config_path))');
    $repo = new ScopeConfigRepository($pdo);
    $resolver = new ScopedSettingValueResolver(new ScopeConfigSettingStore($pdo, $repo), new SettingValueResolver(ConfigRepository::fromArray([])));

    return [$resolver, $repo];
}

it('reports a value saved at the requested scope as database', function (): void {
    [$resolver, $repo] = scopeResolver();
    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Sydney');
    $value = $resolver->resolve(new SettingDefinition('general.timezone', 'Timezone'), new ScopeContext(websiteCode: 'main'));
    expect($value->value)->toBe('Australia/Sydney')
        ->and($value->source)->toBe('database');
});

it('reports a less-specific scoped value as inherited', function (): void {
    [$resolver, $repo] = scopeResolver();
    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Sydney');
    $value = $resolver->resolve(new SettingDefinition('general.timezone', 'Timezone'), new ScopeContext(siteId: 42, storeCode: 'store', websiteCode: 'main'));
    expect($value->source)->toBe('inherited')
        ->and($value->explanation)->toBe('Inherited from the website scope.');
});

it('falls through to the project and default resolver when no scoped row exists', function (): void {
    [$resolver] = scopeResolver();
    $value = $resolver->resolve(new SettingDefinition('mail.enabled', 'Enabled', 'boolean', default: true), ScopeContext::default());
    expect($value->source)->toBe('default')
        ->and($value->value)->toBeTrue();
});

it('redacts secret scoped values', function (): void {
    [$resolver, $repo] = scopeResolver();
    $repo->set('api.secret', ScopeType::Default, null, 'unsafe');
    $value = $resolver->resolve(new SettingDefinition('api.secret', 'Secret', 'secret', secret: true), ScopeContext::default());
    expect($value->source)->toBe('database')
        ->and($value->value)->toBeNull()
        ->and($value->secret)->toBeTrue();
});
