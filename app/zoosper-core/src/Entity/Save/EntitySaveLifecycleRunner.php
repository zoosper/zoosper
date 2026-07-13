<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Coordinates the standard lifecycle stages around an entity save operation.
 *
 * The runner does not perform the database write itself. Instead, callers pass
 * the actual persistence callback, allowing repositories/services to keep SQL
 * ownership while modules can still listen before and after important stages.
 */
final readonly class EntitySaveLifecycleRunner
{
    public function __construct(private EntitySaveEventDispatcherInterface $dispatcher)
    {
    }

    /**
     * @param callable(EntitySaveContext): void $saveCallback
     */
    public function run(EntitySaveContext $context, callable $saveCallback): EntitySaveContext
    {
        $this->dispatcher->dispatch(EntitySaveLifecycle::DATA_COLLECT_BEFORE, $context);
        $this->dispatcher->dispatch(EntitySaveLifecycle::DATA_COLLECT_AFTER, $context);
        $this->dispatcher->dispatch(EntitySaveLifecycle::VALIDATE_BEFORE, $context);
        $this->dispatcher->dispatch(EntitySaveLifecycle::VALIDATE_AFTER, $context);

        if ($context->hasErrors()) {
            return $context;
        }

        $this->dispatcher->dispatch(EntitySaveLifecycle::SAVE_BEFORE, $context);
        if ($context->hasErrors()) {
            return $context;
        }

        $saveCallback($context);

        $this->dispatcher->dispatch(EntitySaveLifecycle::SAVE_AFTER, $context);
        $this->dispatcher->dispatch(EntitySaveLifecycle::COMMIT_AFTER, $context);

        return $context;
    }
}
