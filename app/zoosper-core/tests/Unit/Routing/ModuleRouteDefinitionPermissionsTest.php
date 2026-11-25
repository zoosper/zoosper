<?php

declare(strict_types=1);

use Zoosper\Core\Routing\ModuleRouteDefinition;

test('route permissions retain string values and list order', function (mixed $input, array $expected): void {
    expect(ModuleRouteDefinition::normalisePermissions($input))->toBe($expected);
})->with([
    [null, []],
    ['', []],
    ['0', ['0']],
    [' ', [' ']],
    ['page.view', ['page.view']],
    [false, []],
    [42, []],
    [new stdClass(), []],
    [['first' => 'page.view', 9 => '', 20 => '0', 'invalid' => false, 30 => 'page.view', 40 => ['nested'], 50 => ' '], ['page.view', '0', 'page.view', ' ']],
]);