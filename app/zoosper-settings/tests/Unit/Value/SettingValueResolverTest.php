<?php

declare(strict_types=1);

use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Value\SettingValueResolver;

it('reports configured project values as read-only', function (): void {
    $resolver = new SettingValueResolver(ConfigRepository::fromArray(['admin' => ['base_path' => '/control']]));
    $value = $resolver->resolve(new SettingDefinition('admin.base_path', 'Admin path', default: '/admin'));
    expect($value->value)->toBe('/control')
        ->and($value->source)->toBe('project')
        ->and($value->readOnly)->toBeTrue();
});

it('falls back to module defaults and reports unset values', function (): void {
    $resolver = new SettingValueResolver(ConfigRepository::fromArray([]));
    expect($resolver->resolve(new SettingDefinition('mail.enabled', 'Enabled', 'boolean', default: true))->source)->toBe('default')
        ->and($resolver->resolve(new SettingDefinition('mail.sender', 'Sender'))->source)->toBe('unset');
});

it('never exposes secret values', function (): void {
    $resolver = new SettingValueResolver(ConfigRepository::fromArray(['api' => ['secret' => 'unsafe']]));
    $value = $resolver->resolve(new SettingDefinition('api.secret', 'API secret', 'secret', secret: true));
    expect($value->value)->toBeNull()
        ->and($value->secret)->toBeTrue()
        ->and($value->readOnly)->toBeTrue();
});










