<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;

/**
 * CORRECTNESS FIX REGRESSION TEST — proves Request::form() now reads from
 * a genuinely immutable, constructor-provided property instead of the live
 * $_POST superglobal.
 *
 * File placement: app/zoosper-core/tests/Unit/Http/RequestFormImmutabilityTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
it('returns the form data explicitly passed to the constructor, ignoring $_POST entirely', function (): void {
    // Set $_POST to something DIFFERENT from what we pass to the
    // constructor — if form() were still reading $_POST directly (the old
    // bug), this test would fail, since it would return the wrong data.
    $_POST = ['email' => 'wrong-from-global@example.test'];

    $request = new Request(
        method: 'POST',
        path: '/admin/users/edit',
        form: ['email' => 'correct-from-constructor@example.test'],
    );

    expect($request->form())->toBe(['email' => 'correct-from-constructor@example.test']);

    $_POST = [];
});

it('returns an empty array when no form data was provided and $_POST is non-empty', function (): void {
    // Confirms there is no silent fallback to $_POST when form is omitted —
    // the default is a genuinely empty array, not "whatever $_POST has."
    $_POST = ['should_not_appear' => 'true'];

    $request = new Request(method: 'POST', path: '/admin/users/edit');

    expect($request->form())->toBe([]);

    $_POST = [];
});

it('fromGlobals() captures $_POST once at construction time', function (): void {
    $_POST = ['captured' => 'yes'];
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['REQUEST_URI'] = '/admin/users/edit';

    $request = Request::fromGlobals();

    // Change $_POST AFTER construction — the already-built Request must NOT
    // reflect this change, proving the capture happened once, not on every
    // form() call.
    $_POST = ['captured' => 'changed-after-construction'];

    expect($request->form())->toBe(['captured' => 'yes']);

    $_POST = [];
});

it('withForm() returns a new immutable Request carrying the given form data, leaving other properties unchanged', function (): void {
    $original = new Request(method: 'POST', path: '/admin/users/edit', form: ['a' => '1']);
    $withNewForm = $original->withForm(['b' => '2']);

    expect($original->form())->toBe(['a' => '1']); // original unchanged
    expect($withNewForm->form())->toBe(['b' => '2']);
    expect($withNewForm->path())->toBe($original->path()); // other properties preserved
    expect($withNewForm->method())->toBe($original->method());
});

it('preserves form data through withSiteContext() and withRouteParams()', function (): void {
    $original = new Request(method: 'POST', path: '/admin/users/edit', form: ['email' => 'preserved@example.test']);

    $withRouteParams = $original->withRouteParams(['id' => '42']);

    expect($withRouteParams->form())->toBe(['email' => 'preserved@example.test']);
    expect($withRouteParams->routeParam('id'))->toBe('42');
});










