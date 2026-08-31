<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\BulkAction;

use InvalidArgumentException;

/**
 * Host-provided callables used to bind existing Zoosper security services to
 * the framework-neutral Admin Grid bulk-action boundary.
 */
final readonly class GridBulkHostBindings
{
    /**
     * @param callable(string): bool $csrfValidator
     * @param callable(string): bool $permissionChecker
     * @param callable(\Zoosper\Grid\BulkAction\GridBulkActionDefinition, \Zoosper\Grid\BulkAction\GridBulkSelection): bool $auditReadiness
     */
    public function __construct(
        public mixed $csrfValidator,
        public mixed $permissionChecker,
        public mixed $auditReadiness,
    ) {
        foreach ([
            'CSRF validator' => $csrfValidator,
            'permission checker' => $permissionChecker,
            'audit readiness check' => $auditReadiness,
        ] as $label => $binding) {
            if (!is_callable($binding)) {
                throw new InvalidArgumentException('Grid bulk ' . $label . ' must be callable.');
            }
        }
    }
}











