<?php

declare(strict_types=1);

use Zoosper\Mail\Admin\EmailLogGrid;

it('normalises Email Log Grid scalar filters without casting nested input', function (): void {
    $method = new \ReflectionMethod(EmailLogGrid::class, 'scalarFilter');
    expect($method->invoke(null, '  value  '))->toBe('value')
        ->and($method->invoke(null, 0))->toBe('0')
        ->and($method->invoke(null, false))->toBe('')
        ->and($method->invoke(null, null))->toBe('')
        ->and($method->invoke(null, ['nested']))->toBe('');
});
