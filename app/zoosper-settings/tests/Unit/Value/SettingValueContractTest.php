<?php

declare(strict_types=1);

use Zoosper\Settings\Value\SettingValue;

it('rejects unknown sources and non-redacted secret values', function (): void {
    expect(fn () => new SettingValue('x.path', null, 'unknown', false))->toThrow(\InvalidArgumentException::class)
        ->and(fn () => new SettingValue('x.secret', 'leak', 'project', true, true))->toThrow(\InvalidArgumentException::class);
});










