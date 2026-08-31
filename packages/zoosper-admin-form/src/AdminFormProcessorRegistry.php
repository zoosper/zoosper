<?php

declare(strict_types=1);

namespace Zoosper\AdminForm;

/**
 * Runtime registry for admin form processors.
 *
 * Phase 1.41 (page decoupling, part A): relocated to Zoosper\AdminForm —
 * see AdminFormSection.php for the full reasoning. Logic is completely
 * unchanged.
 */
final class AdminFormProcessorRegistry
{
    /** @var list<AdminFormProcessorInterface> */
    private array $processors = [];

    public function add(AdminFormProcessorInterface $processor): self
    {
        $this->processors[] = $processor;

        return $this;
    }

    /**
     * @param array<string, mixed> $form
     * @param array<string, mixed> $context
     */
    public function process(string $formHandle, array $form, array $context = []): AdminFormProcessingResult
    {
        $result = AdminFormProcessingResult::success();

        foreach ($this->processors as $processor) {
            if ($processor->formHandle() !== $formHandle) {
                continue;
            }

            $result = $result->merge($processor->process($form, $context));
        }

        return $result;
    }
}

