<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Controller\RoleAdminController;

/** @return list<int> */
function roleAdminIdsFromForm(array $form, string $field): array
{
    $controller = (new ReflectionClass(RoleAdminController::class))->newInstanceWithoutConstructor();
    $method = new ReflectionMethod(RoleAdminController::class, 'idsFromForm');

    /** @var list<int> $ids */
    $ids = $method->invoke($controller, $form, $field);

    return $ids;
}

/** @return list<array<string, mixed>> */
function roleAdminNormaliseAclGroups(mixed $groups): array
{
    $controller = (new ReflectionClass(RoleAdminController::class))->newInstanceWithoutConstructor();
    $method = new ReflectionMethod(RoleAdminController::class, 'normaliseAclGroups');

    /** @var list<array<string, mixed>> $normalised */
    $normalised = $method->invoke($controller, $groups);

    return $normalised;
}

it('accepts only positive integer form identifiers while preserving order', function (): void {
    expect(roleAdminIdsFromForm([
        'permission_ids' => ['7', 3, '0', 0, -2, '1.5', 'invalid', null, ['4']],
    ], 'permission_ids'))->toBe([7, 3]);
});

it('returns an empty identifier list for missing and non-array form fields', function (): void {
    expect(roleAdminIdsFromForm([], 'permission_ids'))->toBe([])
        ->and(roleAdminIdsFromForm(['permission_ids' => '7'], 'permission_ids'))->toBe([]);
});

it('normalises ACL configuration to a list of string-keyed records', function (): void {
    expect(roleAdminNormaliseAclGroups([
        ['code' => 'content', 'label' => 'Content', 0 => 'discarded'],
        'invalid',
        ['code' => 'users', 'sort_order' => 20],
    ]))->toBe([
        ['code' => 'content', 'label' => 'Content'],
        ['code' => 'users', 'sort_order' => 20],
    ])->and(roleAdminNormaliseAclGroups('invalid'))->toBe([]);
});
