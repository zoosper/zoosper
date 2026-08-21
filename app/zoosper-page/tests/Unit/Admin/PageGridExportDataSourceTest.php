<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Pagination\Pager;
use Zoosper\Grid\GridCriteria;
use Zoosper\Page\Admin\PageGridExportCriteria;
use Zoosper\Page\Admin\PageGridExportDataSource;
use Zoosper\Page\Admin\PageGridExportRepositoryInterface;

test('export data source ignores screen pager and forwards resolved filters and sorting', function (): void {
    $repository = new class implements PageGridExportRepositoryInterface {
        public ?PageGridExportCriteria $criteria = null;
        public function stream(PageGridExportCriteria $criteria): iterable
        {
            $this->criteria = $criteria;
            yield ['id' => 1, 'title' => 'Landing'];
        }
    };
    $source = new PageGridExportDataSource($repository);
    $rows = iterator_to_array($source->exportRows(new GridCriteria(
        new Pager(8, 20),
        'title',
        'asc',
        ['q' => ' landing ', 'status' => 'published', 'site_id' => ['9', '4', '9']],
    )));

    expect($rows)->toBe([['id' => 1, 'title' => 'Landing']]);
    expect($repository->criteria->search)->toBe('landing');
    expect($repository->criteria->status)->toBe('published');
    expect($repository->criteria->siteIds)->toBe([9, 4]);
    expect($repository->criteria->sortBy)->toBe('title');
    expect($repository->criteria->sortDir)->toBe('asc');
});
