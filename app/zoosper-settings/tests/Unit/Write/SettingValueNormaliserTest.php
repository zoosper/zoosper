<?php

declare(strict_types=1);

use Zoosper\Settings\Definition\SettingDefinition;
use Zoosper\Settings\Write\SettingValidationException;
use Zoosper\Settings\Write\SettingValueNormaliser;

it('normalises supported scalar types', function (): void {
    $normaliser = new SettingValueNormaliser();
    expect($normaliser->normalise(new SettingDefinition('x.bool', 'Bool', 'boolean'), 'on'))->toBe('1')
        ->and($normaliser->normalise(new SettingDefinition('x.int', 'Int', 'integer'), ' 42 '))->toBe('42')
        ->and($normaliser->normalise(new SettingDefinition('x.email', 'Email', 'email'), 'a@example.test'))->toBe('a@example.test')
        ->and($normaliser->normalise(new SettingDefinition('x.select', 'Select', 'select', options: ['a', 'b']), 'b'))->toBe('b');
});

it('rejects invalid, secret and read-only input', function (): void {
    $normaliser = new SettingValueNormaliser();
    expect(fn () => $normaliser->normalise(new SettingDefinition('x.email', 'Email', 'email'), 'bad'))->toThrow(SettingValidationException::class)
        ->and(fn () => $normaliser->normalise(new SettingDefinition('x.secret', 'Secret', 'secret', secret: true), 'x'))->toThrow(SettingValidationException::class)
        ->and(fn () => $normaliser->normalise(new SettingDefinition('x.locked', 'Locked', readOnly: true), 'x'))->toThrow(SettingValidationException::class);
});










