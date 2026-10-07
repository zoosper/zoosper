<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\RoleGridCriteria;

it('normalises Role Grid search filters without casting non-scalars', function (mixed $value, string $expected): void {
    $method = new ReflectionMethod(RoleGridCriteria::class, 'scalarFilter');

    expect($method->invoke(null, $value))->toBe($expected);
})->with([
    'string' => [' value ', ' value '],
    'integer' => [42, '42'],
    'true' => [true, '1'],
    'false' => [false, ''],
    'null' => [null, ''],
    'array' => [['unexpected'], ''],
    'object' => [new stdClass(), ''],
]);
