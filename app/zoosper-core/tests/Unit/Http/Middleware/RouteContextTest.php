<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Http\Middleware;

use Zoosper\Core\Http\Middleware\RouteContext;

test('a null permission normalises to an empty list', function () {
    expect((new RouteContext('GET', '/admin/x', false, null))->requiresAnyPermission())->toBe([]);
});

test('an empty-string permission normalises to an empty list', function () {
    expect((new RouteContext('GET', '/admin/x', false, ''))->requiresAnyPermission())->toBe([]);
});

test('a single-string permission normalises to a one-element list', function () {
    expect((new RouteContext('GET', '/admin/x', false, 'page.manage'))->requiresAnyPermission())->toBe(['page.manage']);
});

test('an array permission is preserved as an OR list', function () {
    expect((new RouteContext('GET', '/admin/users', false, ['role.manage', 'user.manage']))->requiresAnyPermission())
        ->toBe(['role.manage', 'user.manage']);
});

test('empty and non-string array entries are filtered out', function () {
    expect((new RouteContext('POST', '/admin/x', false, ['role.manage', '', 'user.manage']))->requiresAnyPermission())
        ->toBe(['role.manage', 'user.manage']);
});

test('public flag and path are retained alongside permissions', function () {
    $context = new RouteContext('GET', '/admin/login', true, null);

    expect($context->isPublic)->toBeTrue();
    expect($context->path)->toBe('/admin/login');
    expect($context->requiresAnyPermission())->toBe([]);
});