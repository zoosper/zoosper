<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AdminUserGridCriteria;

it('normalises Admin User Grid scalar filters without casting nested input', function (): void {
    $method = new \ReflectionMethod(AdminUserGridCriteria::class, 'scalarFilter');

    expect($method->invoke(null, '  admin  '))->toBe('admin')
        ->and($method->invoke(null, 0))->toBe('0')
        ->and($method->invoke(null, false))->toBe('')
        ->and($method->invoke(null, null))->toBe('')
        ->and($method->invoke(null, ['nested']))->toBe('');
});
