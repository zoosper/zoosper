<?php

declare(strict_types=1);

namespace Zoosper\Core\Form;

/**
 * Immutable admin form section rendered by one core or third-party provider.
 *
 * Section keys are stable extension points. Modules can add new sections by
 * registering additional providers for the same form handle without changing a
 * controller or overriding the whole page form.
 *
 * Phase 1.41 (page decoupling, part A): relocated to Zoosper\Core\Form from
 * the admin module's form namespace. This class has no dependency on
 * AdminUser or anything auth-specific — it is pure, generic data (title,
 * HTML, sort order), so Core is its correct home.
 */
final readonly class AdminFormSection
{
    public function __construct(
        public string $key,
        public string $title,
        public string $html,
        public int $sortOrder = 100,
        public ?string $description = null,
        public ?string $modifierClass = null,
    ) {
    }
}
