<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageGridExportCriteria;

it('normalises Page export scalar filters without casting nested input', function (): void {
    $method = new \ReflectionMethod(PageGridExportCriteria::class, 'scalarFilter');

    expect($method->invoke(null, '  exported  '))->toBe('exported')
        ->and($method->invoke(null, 0))->toBe('0')
        ->and($method->invoke(null, false))->toBe('')
        ->and($method->invoke(null, null))->toBe('')
        ->and($method->invoke(null, ['nested']))->toBe('');
});
