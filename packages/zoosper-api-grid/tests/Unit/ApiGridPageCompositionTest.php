<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Tests\Unit;

use InvalidArgumentException;
use Zoosper\ApiGrid\Definition\ApiGridDefinition;
use Zoosper\ApiGrid\Definition\ApiGridRegistry;
use Zoosper\ApiGrid\Page\ApiGridPageBuilder;
use Zoosper\ApiGrid\Page\ApiGridQueryFactory;
use Zoosper\Grid\DataSource\GridDataSourceCapabilities;
use Zoosper\Grid\DataSource\GridDataSourceInterface;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\Grid\DataSource\GridResult;
use Zoosper\Grid\GridDefinition;

function apiGridDefinitionForPageTest(): ApiGridDefinition
{
    return new ApiGridDefinition(
        key: 'sample.records',
        title: 'Sample Records',
        route: '/admin/sample-records',
        permission: 'sample.view',
        dataSourceService: 'sample.source',
        grid: new GridDefinition('sample', [], [], 'id', 'asc'),
        pageSizes: [5, 20, 50],
    );
}

it('composes a registered page using only source-supported controls', function (): void {
    $source = new class implements GridDataSourceInterface {
        public ?GridQuery $query = null;
        public function capabilities(): GridDataSourceCapabilities
        {
            return new GridDataSourceCapabilities(
                searchable: false,
                sortableColumns: ['created_at'],
                filterableFields: ['status'],
            );
        }
        public function fetch(GridQuery $query): GridResult
        {
            $this->query = $query;
            return new GridResult([['id' => 1]], 1, $query->page, $query->pageSize);
        }
    };
    $builder = new ApiGridPageBuilder(
        new ApiGridRegistry([apiGridDefinitionForPageTest()]),
        new ApiGridQueryFactory(),
        static fn (string $service): object => $source,
    );

    $page = $builder->build('sample.records', [
        'page' => '2',
        'page_size' => '5',
        'sort' => 'created_at',
        'dir' => 'desc',
        'q' => 'must be ignored',
        'filters' => ['status' => 'active', 'private' => 'ignored'],
    ]);

    expect($page->query->page)->toBe(2)
        ->and($page->query->pageSize)->toBe(5)
        ->and($page->query->sort)->toBe('created_at')
        ->and($page->query->direction)->toBe('desc')
        ->and($page->query->search)->toBeNull()
        ->and($page->query->filters)->toBe(['status' => 'active'])
        ->and($page->result->items)->toBe([['id' => 1]]);
});

it('rejects duplicate registrations and invalid data-source services', function (): void {
    $definition = apiGridDefinitionForPageTest();
    $registry = new ApiGridRegistry([$definition]);
    expect(fn () => $registry->register($definition))->toThrow(InvalidArgumentException::class, 'Duplicate');

    $builder = new ApiGridPageBuilder($registry, new ApiGridQueryFactory(), static fn (): object => new \stdClass());
    expect(fn () => $builder->build('sample.records', []))->toThrow(InvalidArgumentException::class, 'GridDataSourceInterface');
});
