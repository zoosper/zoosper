<?php

declare(strict_types=1);

use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkActionDispatcher;
use Zoosper\Grid\BulkAction\GridBulkActionExecutionResult;
use Zoosper\Grid\BulkAction\GridBulkActionExecutorInterface;
use Zoosper\Grid\BulkAction\GridBulkActionExecutorRegistry;
use Zoosper\Grid\BulkAction\GridBulkActionRegistry;
use Zoosper\Grid\BulkAction\GridBulkActionRequest;
use Zoosper\Grid\BulkAction\GridBulkConfirmationPolicy;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelection;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;

function gridTestExecutor(): GridBulkActionExecutorInterface
{
    return new class implements GridBulkActionExecutorInterface {
        public function gridKey(): string { return 'admin.pages'; }
        public function actionId(): string { return 'page.publish'; }
        public function execute(GridBulkActionDefinition $definition, GridBulkSelection $selection, \Zoosper\Grid\BulkAction\GridBulkExecutionContext $context): GridBulkActionExecutionResult
        {
            return GridBulkActionExecutionResult::success('Executed.', ['selected' => $selection->count()]);
        }
    };
}

it('dispatches a validated explicit selection to the matching executor', function (): void {
    $definitions = new GridBulkActionRegistry();
    $definitions->register('admin.pages', new GridBulkActionDefinition(
        'page.publish', 'Publish', GridBulkSelectionScope::EXPLICIT_IDENTITIES,
        GridBulkExecutionType::SERVER_MUTATION, GridBulkConfirmationPolicy::CONFIRM,
        maximumSelection: 2, auditRequired: true,
    ));
    $executors = new GridBulkActionExecutorRegistry();
    $executors->register(gridTestExecutor());
    $result = (new GridBulkActionDispatcher($definitions, $executors))->dispatch(
        new GridBulkActionRequest('admin.pages', 'page.publish', [3, '3', 2], new \Zoosper\Grid\BulkAction\GridBulkExecutionContext(new \Zoosper\Grid\BulkAction\GridBulkActor(1))),
    );
    expect($result->successful)->toBeTrue()->and($result->context['selected'])->toBe(2);
});

it('rejects client downloads at the server boundary', function (): void {
    $definitions = new GridBulkActionRegistry();
    $definitions->register('admin.pages', new GridBulkActionDefinition(
        'export.selected', 'Export selected', GridBulkSelectionScope::EXPLICIT_IDENTITIES,
        GridBulkExecutionType::CLIENT_DOWNLOAD,
    ));
    expect(fn () => (new GridBulkActionDispatcher($definitions, new GridBulkActionExecutorRegistry()))
        ->dispatch(new GridBulkActionRequest('admin.pages', 'export.selected', [1])), new \Zoosper\Grid\BulkAction\GridBulkExecutionContext(new \Zoosper\Grid\BulkAction\GridBulkActor(1)))
        ->toThrow(InvalidArgumentException::class, 'not server executable');
});

it('rejects selections above the declared maximum before execution', function (): void {
    $definitions = new GridBulkActionRegistry();
    $definitions->register('admin.pages', new GridBulkActionDefinition(
        'page.publish', 'Publish', GridBulkSelectionScope::EXPLICIT_IDENTITIES,
        GridBulkExecutionType::SERVER_MUTATION, GridBulkConfirmationPolicy::CONFIRM,
        maximumSelection: 1,
    ));
    $executors = new GridBulkActionExecutorRegistry();
    $executors->register(gridTestExecutor());
    expect(fn () => (new GridBulkActionDispatcher($definitions, $executors))
        ->dispatch(new GridBulkActionRequest(
            'admin.pages',
            'page.publish',
            [1, 2],
            new \Zoosper\Grid\BulkAction\GridBulkExecutionContext(
                new \Zoosper\Grid\BulkAction\GridBulkActor(1),
            ),
        )))
        ->toThrow(InvalidArgumentException::class, 'maximum');
});

it('rejects duplicate executor registrations', function (): void {
    $registry = new GridBulkActionExecutorRegistry();
    $registry->register(gridTestExecutor());
    expect(fn () => $registry->register(gridTestExecutor()))
        ->toThrow(InvalidArgumentException::class, 'already registered');
});











