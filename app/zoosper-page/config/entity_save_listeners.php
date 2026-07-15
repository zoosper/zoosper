<?php

declare(strict_types=1);

/**
 * Page module entity-save lifecycle listeners.
 *
 * Discovered automatically by ModuleEntitySaveListenerLoader (Phase 1.28).
 * Third-party modules can add their own config/entity_save_listeners.php the same
 * way, without editing any core file.
 */

use Zoosper\Core\Entity\Save\EntitySaveLifecycle;
use Zoosper\Page\Save\PageSaveValidationListener;

return [
    EntitySaveLifecycle::VALIDATE_AFTER => [
        PageSaveValidationListener::class,
    ],
];
