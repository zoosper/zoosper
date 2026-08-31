<?php

declare(strict_types=1);

use Zoosper\Pagination\PaginationResult;

it('preserves numbered pagination semantics behind the Marko adapter', function (): void {
    $result = new PaginationResult(items: [['id' => 21]], total: 41, page: 2, pageSize: 20);

    expect($result->items)->toBe([['id' => 21]])
        ->and($result->total)->toBe(41)
        ->and($result->page)->toBe(2)
        ->and($result->pageSize)->toBe(20)
        ->and($result->totalPages())->toBe(3)
        ->and($result->hasPrevious())->toBeTrue()
        ->and($result->hasNext())->toBeTrue();
});

it('keeps empty result pages valid', function (): void {
    $result = new PaginationResult(items: [], total: 0, page: 1, pageSize: 20);

    expect($result->totalPages())->toBe(1)
        ->and($result->hasPrevious())->toBeFalse()
        ->and($result->hasNext())->toBeFalse();
});

it('preserves direct-constructor edge semantics while Pager normalizes requests', function (): void {
    $result = new PaginationResult(items: [], total: 0, page: 0, pageSize: 0);

    expect($result->totalPages())->toBe(1)
        ->and($result->hasPrevious())->toBeFalse()
        ->and($result->hasNext())->toBeTrue();
});











