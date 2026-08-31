<?php

declare(strict_types=1);

namespace Zoosper\Core\Module;

use Zoosper\Errors\ZoosperException;

final class DuplicateModuleException extends ZoosperException
{
    public static function crossLayer(Module $first, Module $second): self
    {
        $winner = ModuleRegistry::sourcePriority($first->source) > ModuleRegistry::sourcePriority($second->source)
            ? $first
            : $second;
        $shadowed = $winner === $first ? $second : $first;

        return new self(sprintf(
            "Module identity '%s' is declared across discovery layers. '%s' (%s) would shadow '%s' (%s). Remove the stale copy before continuing.",
            $winner->name,
            $winner->path,
            $winner->source,
            $shadowed->path,
            $shadowed->source,
        ));
    }

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










