<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

/**
 * Processes submitted values for a named admin form handle.
 *
 * Third-party modules can implement this interface to validate and prepare
 * module-owned fields without editing core controllers.
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
