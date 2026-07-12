<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

/**
 * Provides one or more sections for a named admin form handle.
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
