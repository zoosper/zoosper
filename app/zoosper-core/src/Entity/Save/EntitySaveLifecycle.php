<?php

declare(strict_types=1);

namespace Zoosper\Core\Entity\Save;

/**
 * Standard lifecycle event names for modular entity save flows.
 */
final class EntitySaveLifecycle
{
    public const DATA_COLLECT_BEFORE = 'entity_save.data_collect.before';
    public const DATA_COLLECT_AFTER = 'entity_save.data_collect.after';
    public const VALIDATE_BEFORE = 'entity_save.validate.before';
    public const VALIDATE_AFTER = 'entity_save.validate.after';
    public const SAVE_BEFORE = 'entity_save.save.before';
    public const SAVE_AFTER = 'entity_save.save.after';
    public const COMMIT_AFTER = 'entity_save.commit.after';

    private function __construct()
    {
    }
}
