<?php

declare(strict_types=1);

use Zoosper\AdminGrid\BulkAction\GridBulkAuditGuard;
use Zoosper\AdminGrid\BulkAction\GridBulkCsrfVerifier;
use Zoosper\AdminGrid\BulkAction\GridBulkExecutionResultAdapter;
use Zoosper\AdminGrid\BulkAction\GridBulkPermissionChecker;
use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkActionExecutionResult;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelection;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;

it('adapts the host CSRF validator fail closed', function (): void {
    $verifier = new GridBulkCsrfVerifier(static fn (string $token): bool => hash_equals('valid', $token));
    $verifier->assertValid('valid');
    expect(fn () => $verifier->assertValid('invalid'))->toThrow(InvalidArgumentException::class, 'Invalid');
    expect(fn () => $verifier->assertValid(''))->toThrow(InvalidArgumentException::class, 'Invalid');
});

it('adapts authenticated permission checks', function (): void {
    $checker = new GridBulkPermissionChecker(static fn (string $permission): bool => $permission === 'page.manage');
    expect($checker->isAllowed('page.manage'))->toBeTrue()
        ->and($checker->isAllowed('role.manage'))->toBeFalse()
        ->and($checker->isAllowed(''))->toBeFalse();
});

it('fails closed when required audit infrastructure is unavailable', function (): void {
    $definition = new GridBulkActionDefinition(
        'page.publish', 'Publish', GridBulkSelectionScope::EXPLICIT_IDENTITIES,
        GridBulkExecutionType::SERVER_MUTATION,
        \Zoosper\Grid\BulkAction\GridBulkConfirmationPolicy::CONFIRM,
        auditRequired: true,
    );
    $selection = new GridBulkSelection([1], 100);
    $guard = new GridBulkAuditGuard(static fn (): bool => false);
    expect(fn () => $guard->assertAvailable($definition, $selection))
        ->toThrow(InvalidArgumentException::class, 'unavailable');
});

it('adapts successful execution to a 303 redirect and failure to 422', function (): void {
    $adapter = new GridBulkExecutionResultAdapter();
    $success = $adapter->adapt(GridBulkActionExecutionResult::success('Done.'), '/admin/pages');
    $failure = $adapter->adapt(GridBulkActionExecutionResult::failure('Unable.'), '/admin/pages');
    expect($success->status)->toBe(303)->and($success->redirectPath)->toBe('/admin/pages')
        ->and($failure->status)->toBe(422)->and($failure->redirectPath)->toBeNull();
});

it('rejects unsafe success redirects', function (string $path): void {
    expect(fn () => (new GridBulkExecutionResultAdapter())->adapt(
        GridBulkActionExecutionResult::success('Done.'), $path,
    ))->toThrow(InvalidArgumentException::class, 'redirect path');
})->with(['https://example.test/admin', '//example.test/admin', 'admin/pages', '']);











