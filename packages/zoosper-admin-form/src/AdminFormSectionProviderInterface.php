<?php

declare(strict_types=1);

namespace Zoosper\AdminForm;

/**
 * Provides one or more sections for a named admin form handle.
 *
 * Phase 1.41 (page decoupling, part A): relocated to Zoosper\AdminForm —
 * see AdminFormSection.php for the full reasoning.
 */
interface AdminFormSectionProviderInterface
{
    public function formHandle(): string;

    /**
     * @param array<string, mixed> $context Form-specific render context.
     *
     * @return iterable<AdminFormSection>
     */
    public function sections(array $context): iterable;
}












