<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Config\Scope\ScopeConfigRepository;
use Zoosper\Core\Config\Scope\ScopeContext;
use Zoosper\Core\Config\Scope\ScopeType;
use Zoosper\Theme\Config\TemplateRuntimeConfig;

function themeScopeRepository(): ScopeConfigRepository
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL)');
    return new ScopeConfigRepository($pdo);
}

it('preserves project defaults and resolves a project-relative cache directory', function (): void {
    $config = new TemplateRuntimeConfig('/srv/zoosper', ConfigRepository::fromArray(['template' => ['engine' => 'php', 'template_cache_path' => 'var/custom']]));
    expect($config->engine())->toBe('php')
        ->and($config->cacheDirectory())->toBe('/srv/zoosper/var/custom');
});

it('lets Default-scope database values override project template runtime values', function (): void {
    $scoped = themeScopeRepository();
    $scoped->set('template.engine', ScopeType::Default, null, 'php');
    $scoped->set('template.template_cache_path', ScopeType::Default, null, 'var/runtime-templates');
    $config = new TemplateRuntimeConfig('/srv/zoosper', ConfigRepository::fromArray([]), $scoped, ScopeContext::default());
    expect($config->engine())->toBe('php')
        ->and($config->cacheDirectory())->toBe('/srv/zoosper/var/runtime-templates');
});

it('falls back safely for unsupported engine and empty cache values', function (): void {
    $config = new TemplateRuntimeConfig('/srv/zoosper', ConfigRepository::fromArray(['template' => ['engine' => 'twig', 'template_cache_path' => '']]));
    expect($config->engine())->toBe('latte')
        ->and($config->cacheDirectory())->toBe('/srv/zoosper/var/cache/templates');
});
