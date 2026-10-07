<?php

declare(strict_types=1);

use Zoosper\Core\Http\TrustedProxyResolver;

it('normalises trusted-proxy environment values without casting non-scalars', function (mixed $value, string $expected): void {
    $method = new ReflectionMethod(TrustedProxyResolver::class, 'scalarString');

    expect($method->invoke(null, $value))->toBe($expected);
})->with([
    'string' => ['10.0.0.0/24', '10.0.0.0/24'],
    'integer' => [42, '42'],
    'true' => [true, '1'],
    'false' => [false, ''],
    'null' => [null, ''],
    'array' => [['10.0.0.0/24'], ''],
    'object' => [new stdClass(), ''],
]);
