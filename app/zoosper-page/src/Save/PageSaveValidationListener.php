<?php

declare(strict_types=1);

namespace Zoosper\Page\Save;

use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\EntitySaveEventListenerInterface;

/**
 * Real, reusable validation for page saves via the entity save lifecycle.
 *
 * This is production validation, not a demo. It listens on the VALIDATE_AFTER
 * stage and rejects invalid page submissions by adding structured errors to the
 * context, which causes EntitySaveLifecycleRunner to abort before persistence.
 *
 * The listener scopes itself to the 'page' entity type, so it is inert for any
 * other entity that shares the VALIDATE_AFTER event (e.g. admin_user).
 *
 * PCI-aware: only reads CMS page fields; never touches or logs secrets/tokens.
 */
final class PageSaveValidationListener implements EntitySaveEventListenerInterface
{
    private const MIN_TITLE_LENGTH = 3;

    public function handle(EntitySaveContext $context): void
    {
        if ($context->entityType() !== 'page') {
            return;
        }

        $data = $context->data();

        $title = trim((string) $data->getData('title', ''));
        if ($title === '') {
            $context->addError('title', 'Title is required.');
        } elseif (mb_strlen($title) < self::MIN_TITLE_LENGTH) {
            $context->addError('title', 'Title must be at least 3 characters.');
        }

        $siteId = (int) $data->getData('site_id', 0);
        if ($siteId <= 0) {
            $context->addError('site_id', 'Please choose a site.');
        }
    }
}
