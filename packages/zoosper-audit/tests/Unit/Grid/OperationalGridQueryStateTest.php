<?php

declare(strict_types=1);

use Zoosper\Audit\Admin\Grid\OperationalGridQueryState;
use Zoosper\Core\Http\Request;

it('accepts only representable positive bookmark identifiers', function (mixed $value, ?int $expected): void {
    $request = new Request('GET', '/', query: ['bookmark' => $value]);

    expect(OperationalGridQueryState::bookmarkId($request))->toBe($expected);
})->with([
    'one' => ['1', 1],
    'maximum integer' => [(string) PHP_INT_MAX, PHP_INT_MAX],
    'overflow' => [(string) PHP_INT_MAX . '0', null],
    'zero' => ['0', null],
    'leading zero' => ['01', null],
    'negative' => ['-1', null],
    'array' => [['1'], null],
    'missing' => [null, null],
]);