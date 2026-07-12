<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

/**
 * Immutable admin form section rendered by one core or third-party provider.
 *
 * Section keys are stable extension points. Modules can add new sections by
 * registering additional providers for the same form handle without changing a
 * controller or overriding the whole page form.
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
