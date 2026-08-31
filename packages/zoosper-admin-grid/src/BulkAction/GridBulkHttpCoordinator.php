<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

use InvalidArgumentException;
use Zoosper\Grid\BulkAction\GridBulkActionDispatcher;
use Zoosper\Grid\BulkAction\GridBulkActionExecutionResult;
use Zoosper\Grid\BulkAction\GridBulkActionRegistry;
use Zoosper\Grid\BulkAction\GridBulkExecutionContext;
use Zoosper\Grid\BulkAction\GridBulkSelection;

/** Enforces HTTP security gates before entering the shared dispatcher. */
final readonly class GridBulkHttpCoordinator
{
    public function __construct(
        private GridBulkHttpRequestParser $parser,
        private GridBulkActionRegistry $definitions,
        private GridBulkActionDispatcher $dispatcher,
        private GridBulkCsrfVerifierInterface $csrf,
        private GridBulkPermissionCheckerInterface $permissions,
        private GridBulkAuditGuardInterface $audit,
        private GridBulkConfirmationGuard $confirmation,
    ) {
    }

    public function execute(
        string $gridKey,
        GridBulkHttpRequest $request,
        ?GridBulkExecutionContext $executionContext = null,
    ): GridBulkActionExecutionResult {
        $this->csrf->assertValid(trim((string) ($request->form['_csrf'] ?? '')));
        $bulkRequest = $this->parser->parse($gridKey, $request, $executionContext);
        $definition = $this->definitions->require($gridKey, $bulkRequest->actionId);

        if ($definition->requiredPermission !== null
            && !$this->permissions->isAllowed($definition->requiredPermission)) {
            throw new InvalidArgumentException('Grid bulk action permission denied.');
        }

        $this->confirmation->assertConfirmed($definition, $request->form);
        $selection = new GridBulkSelection(
            $bulkRequest->selectedIdentities,
            $definition->maximumSelection,
        );
        if ($definition->auditRequired) {
            $this->audit->assertAvailable($definition, $selection);
        }

        return $this->dispatcher->dispatch($bulkRequest);
    }
}











