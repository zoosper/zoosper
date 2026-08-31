<?php

declare(strict_types=1);

use Zoosper\AdminGrid\BulkAction\GridBulkHostBindings;
use Zoosper\AdminGrid\BulkAction\GridBulkHttpCoordinatorFactory;
use Zoosper\AdminGrid\BulkAction\GridBulkHttpRequest;
use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkActionExecutionResult;
use Zoosper\Grid\BulkAction\GridBulkActionExecutorInterface;
use Zoosper\Grid\BulkAction\GridBulkActionExecutorRegistry;
use Zoosper\Grid\BulkAction\GridBulkActionRegistry;
use Zoosper\Grid\BulkAction\GridBulkConfirmationPolicy;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelection;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;

function protectedCoordinatorFixture(
    bool $csrfAllowed = true,
    bool $permissionAllowed = true,
    bool $auditAvailable = true,
): \Zoosper\AdminGrid\BulkAction\GridBulkHttpCoordinator {
    $definitions = new GridBulkActionRegistry();
    $definitions->register('admin.pages', new GridBulkActionDefinition(
        id: 'page.publish',
        label: 'Publish selected',
        selectionScope: GridBulkSelectionScope::EXPLICIT_IDENTITIES,
        executionType: GridBulkExecutionType::SERVER_MUTATION,
        confirmationPolicy: GridBulkConfirmationPolicy::CONFIRM,
        requiredPermission: 'page.manage',
        maximumSelection: 2,
        auditRequired: true,
    ));

    $executors = new GridBulkActionExecutorRegistry();
    $executors->register(new class implements GridBulkActionExecutorInterface {
        public function gridKey(): string { return 'admin.pages'; }
        public function actionId(): string { return 'page.publish'; }
        public function execute(GridBulkActionDefinition $definition, GridBulkSelection $selection, \Zoosper\Grid\BulkAction\GridBulkExecutionContext $context): GridBulkActionExecutionResult
        {
            return GridBulkActionExecutionResult::success(
                'Published selected Pages.',
                ['selected' => $selection->count()],
            );
        }
    });

    return (new GridBulkHttpCoordinatorFactory())->create(
        $definitions,
        $executors,
        new GridBulkHostBindings(
            csrfValidator: static fn (string $token): bool => $csrfAllowed && $token === 'valid-token',
            permissionChecker: static fn (string $permission): bool => $permissionAllowed && $permission === 'page.manage',
            auditReadiness: static fn (): bool => $auditAvailable,
        ),
    );
}

it('executes only after every protected HTTP gate passes', function (): void {
    $result = protectedCoordinatorFixture()->execute('admin.pages', new GridBulkHttpRequest('POST', [
        '_csrf' => 'valid-token',
        'bulk_action' => 'page.publish',
        'confirmed_action' => 'page.publish',
        'selected_ids' => ['3', '3', '2'],
    ]), new \Zoosper\Grid\BulkAction\GridBulkExecutionContext(new \Zoosper\Grid\BulkAction\GridBulkActor(1)));

    expect($result->successful)->toBeTrue()
        ->and($result->message)->toBe('Published selected Pages.')
        ->and($result->context['selected'])->toBe(2);
});

it('fails before execution when CSRF validation fails', function (): void {
    expect(fn () => protectedCoordinatorFixture(csrfAllowed: false)->execute(
        'admin.pages',
        new GridBulkHttpRequest('POST', [
            '_csrf' => 'invalid', 'bulk_action' => 'page.publish',
            'confirmed_action' => 'page.publish', 'selected_ids' => ['1'],
        ]),
    ))->toThrow(InvalidArgumentException::class, 'CSRF');
});

it('fails before execution when permission is denied', function (): void {
    expect(fn () => protectedCoordinatorFixture(permissionAllowed: false)->execute(
        'admin.pages',
        new GridBulkHttpRequest('POST', [
            '_csrf' => 'valid-token', 'bulk_action' => 'page.publish',
            'confirmed_action' => 'page.publish', 'selected_ids' => ['1'],
        ]),
    ))->toThrow(InvalidArgumentException::class, 'permission denied');
});

it('fails before execution when confirmation is absent', function (): void {
    expect(fn () => protectedCoordinatorFixture()->execute(
        'admin.pages',
        new GridBulkHttpRequest('POST', [
            '_csrf' => 'valid-token', 'bulk_action' => 'page.publish', 'selected_ids' => ['1'],
        ]),
    ))->toThrow(InvalidArgumentException::class, 'explicit confirmation');
});

it('fails before execution when audit infrastructure is unavailable', function (): void {
    expect(fn () => protectedCoordinatorFixture(auditAvailable: false)->execute(
        'admin.pages',
        new GridBulkHttpRequest('POST', [
            '_csrf' => 'valid-token', 'bulk_action' => 'page.publish',
            'confirmed_action' => 'page.publish', 'selected_ids' => ['1'],
        ]),
    ))->toThrow(InvalidArgumentException::class, 'Audit infrastructure is unavailable');
});

it('enforces the declared maximum before execution', function (): void {
    expect(fn () => protectedCoordinatorFixture()->execute(
        'admin.pages',
        new GridBulkHttpRequest('POST', [
            '_csrf' => 'valid-token', 'bulk_action' => 'page.publish',
            'confirmed_action' => 'page.publish', 'selected_ids' => ['1', '2', '3'],
        ]),
    ))->toThrow(InvalidArgumentException::class, 'maximum');
});











