<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspacePageSizeOptions;
use Zoosper\AdminGrid\GridWorkspacePageSizeRenderer;

test('page-size selector is allow-listed and marks the resolved value', function (): void {
    $options = new GridWorkspacePageSizeOptions([20, 50, 100, 200]);
    $html = (new GridWorkspacePageSizeRenderer($options))->render(50);

    expect($html)->toContain('name="page_size"')
        ->toContain('value="50" selected')
        ->toContain('value="200"')
        ->not->toContain('value="5000"');
});

test('unsupported requested size falls back to the first server option', function (): void {
    $options = new GridWorkspacePageSizeOptions([20, 50, 100]);

    expect($options->normalise(999))->toBe(20);
});

test('page-size configuration rejects unsafe or unordered values', function (): void {
    expect(fn () => new GridWorkspacePageSizeOptions([20, 20]))
        ->toThrow(\InvalidArgumentException::class, 'unique');
    expect(fn () => new GridWorkspacePageSizeOptions([50, 20]))
        ->toThrow(\InvalidArgumentException::class, 'ascending');
    expect(fn () => new GridWorkspacePageSizeOptions([20, 1000]))
        ->toThrow(\InvalidArgumentException::class, 'between 1 and 500');
});
