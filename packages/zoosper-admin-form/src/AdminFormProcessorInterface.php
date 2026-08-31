<?php

declare(strict_types=1);

namespace Zoosper\AdminForm;

/**
 * Processes submitted values for a named admin form handle.
 *
 * Phase 1.41 (page decoupling, part A): relocated to Zoosper\AdminForm —
 * see AdminFormSection.php for the full reasoning.
 */
interface AdminFormProcessorInterface
{
    public function formHandle(): string;

    /**
     * @param array<string, mixed> $form Submitted form values.
     * @param array<string, mixed> $context Runtime context such as entity/page/user.
     */
    public function process(array $form, array $context = []): AdminFormProcessingResult;
}












