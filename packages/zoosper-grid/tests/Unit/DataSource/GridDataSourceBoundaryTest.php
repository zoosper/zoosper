<?php

declare(strict_types=1);

namespace Zoosper\Grid\Tests\Unit\DataSource;

use InvalidArgumentException;
use Zoosper\Grid\DataSource\GridDataSourceCapabilities;
use Zoosper\Grid\DataSource\GridDataSourceInterface;
use Zoosper\Grid\DataSource\GridPaginationMode;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\Grid\DataSource\GridResult;

it('supports a transport-neutral numbered collection source', function (): void {
    $source = new class implements GridDataSourceInterface {
        public function capabilities(): GridDataSourceCapabilities
        {
            return new GridDataSourceCapabilities(
                sortableColumns: ['created_at'],
                filterableFields: ['status'],
            );
        }

        public function fetch(GridQuery $query): GridResult
        {
            return new GridResult(
                items: [['id' => 1]],
                total: 41,
                page: $query->page,
                pageSize: $query->pageSize,
            );
        }
    };

    $query = new GridQuery(page: 2, pageSize: 20, filters: ['status' => 'active']);
    $result = $source->fetch($query);

    expect($result->items)->toBe([['id' => 1]])
        ->and($result->totalPages())->toBe(3)
        ->and($source->capabilities()->supportsSort('created_at'))->toBeTrue()
        ->and($source->capabilities()->supportsFilter('status'))->toBeTrue()
        ->and($source->capabilities()->supportsFilter('unknown'))->toBeFalse();
});

it('represents cursor pagination without pretending it is numbered', function (): void {
    $result = new GridResult(
        items: [['id' => 10]],
        total: 1,
        page: 1,
        pageSize: 20,
        paginationMode: GridPaginationMode::Cursor,
        nextCursor: 'next-token',
    );

    expect($result->paginationMode)->toBe(GridPaginationMode::Cursor)
        ->and($result->nextCursor)->toBe('next-token');
});

it('rejects invalid query and result states', function (): void {
    expect(fn (): GridQuery => new GridQuery(page: 0))->toThrow(InvalidArgumentException::class)
        ->and(fn (): GridQuery => new GridQuery(direction: 'sideways'))->toThrow(InvalidArgumentException::class)
        ->and(fn (): GridResult => new GridResult([], -1, 1, 20))->toThrow(InvalidArgumentException::class)
        ->and(fn (): GridResult => new GridResult(
            [],
            0,
            1,
            20,
            GridPaginationMode::Numbered,
            nextCursor: 'invalid',
        ))->toThrow(InvalidArgumentException::class);
});










