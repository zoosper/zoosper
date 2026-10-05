<?php

declare(strict_types=1);

use Zoosper\Core\Console\ConsoleOptions;

it('preserves option values after the first equals sign', function (): void {
    expect(ConsoleOptions::parse(['command', '--token=alpha=beta', '--empty=']))
        ->toBe(['token' => 'alpha=beta', 'empty' => '']);
});

it('normalises slugs without truthy fallback semantics', function (): void {
    expect(ConsoleOptions::slugify('Hello, World!'))->toBe('hello-world')
        ->and(ConsoleOptions::slugify('---'))->toBe('page')
        ->and(ConsoleOptions::slugify(''))->toBe('page');
});
