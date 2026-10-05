<?php

declare(strict_types=1);

use Zoosper\Media\Admin\Grid\MediaGridSource;

it('normalises Media Grid scalar filters without casting nested input', function (): void {
    $method = new \ReflectionMethod(MediaGridSource::class, 'scalarFilter');
    expect($method->invoke(null, '  value  '))->toBe('value')
        ->and($method->invoke(null, 0))->toBe('0')
        ->and($method->invoke(null, false))->toBe('')
        ->and($method->invoke(null, null))->toBe('')
        ->and($method->invoke(null, ['nested']))->toBe('');
});
