<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

use Zoosper\Errors\ZoosperException;

/**
 * Raised when two different modules claim the same identity at one priority.
 */
final class DuplicateModuleException extends ZoosperException
{
    public static function sameLayer(Module $first, Module $second): self
    {
        return new self(
            message: sprintf(
                'Duplicate module identity "%s" was discovered in the "%s" layer.',
                $first->name,
                $first->source,
            ),
            context: sprintf('First path: %s; second path: %s.', $first->path, $second->path),
            suggestion: 'Remove or rename one module. Modules at the same priority must have unique identities.',
            details: [
                'module' => $first->name,
                'source' => $first->source,
                'firstPath' => $first->path,
                'secondPath' => $second->path,
            ],
        );
    }
}
