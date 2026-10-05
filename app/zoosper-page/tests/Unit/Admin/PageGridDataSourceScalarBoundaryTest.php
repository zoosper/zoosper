<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageGridDataSource;

it('normalises Page Grid scalar filters without casting nested input', function (): void {
    $method = new \ReflectionMethod(PageGridDataSource::class, 'scalarFilter');

    expect($method->invoke(null, '  pages  '))->toBe('pages')
        ->and($method->invoke(null, 0))->toBe('0')
        ->and($method->invoke(null, false))->toBe('')
        ->and($method->invoke(null, null))->toBe('')
        ->and($method->invoke(null, ['nested']))->toBe('');
});
