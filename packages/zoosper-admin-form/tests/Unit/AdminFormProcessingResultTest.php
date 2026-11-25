<?php

declare(strict_types=1);

use Zoosper\AdminForm\AdminFormProcessingResult;

it('merges errors into a list and recursively replaces payload values', function (): void {
    $first = new AdminFormProcessingResult(true, [4 => 'first', 'shared' => 'old'], [
        'nested' => ['kept' => 1, 'replaced' => 'old'],
    ]);
    $second = new AdminFormProcessingResult(false, ['shared' => 'new', 8 => 'last'], [
        'nested' => ['replaced' => 'new'],
    ]);

    $result = $first->merge($second);

    expect($result->valid)->toBeFalse()
        ->and($result->errors)->toBe(['first', 'new', 'last'])
        ->and($result->payload)->toBe(['nested' => ['kept' => 1, 'replaced' => 'new']]);
});

it('preserves ordered list errors and combines validity', function (): void {
    $result = AdminFormProcessingResult::success()->merge(AdminFormProcessingResult::failure(['one', 'two']));

    expect($result->valid)->toBeFalse()
        ->and($result->errors)->toBe(['one', 'two'])
        ->and(AdminFormProcessingResult::success()->merge(AdminFormProcessingResult::success())->valid)->toBeTrue();
});