<?php

declare(strict_types=1);

namespace Zoosper\Admin\Form;

use InvalidArgumentException;

/**
 * Builds an admin form processor registry from configuration.
 */
final readonly class AdminFormProcessorConfigFactory
{
    /**
     * @param array<string, mixed> $config Raw aggregated admin form config.
     * @param array<string, list<class-string<AdminFormProcessorInterface>>> $fallbackProcessors
     */
    public function create(array $config, array $fallbackProcessors = []): AdminFormProcessorRegistry
    {
        $processorsByHandle = $this->normalise($config);
        if ($processorsByHandle === []) {
            $processorsByHandle = $fallbackProcessors;
        }

        $registry = new AdminFormProcessorRegistry();
        foreach ($processorsByHandle as $formHandle => $processorClasses) {
            foreach ($processorClasses as $processorClass) {
                $processor = $this->instantiateProcessor($processorClass);
                if ($processor->formHandle() !== $formHandle) {
                    throw new InvalidArgumentException(
                        'Admin form processor ' . $processorClass . ' declares handle ' . $processor->formHandle() . ' but was registered for ' . $formHandle . '.',
                    );
                }

                $registry->add($processor);
            }
        }

        return $registry;
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, list<class-string<AdminFormProcessorInterface>>>
     */
    private function normalise(array $config): array
    {
        $processors = isset($config['processors']) && is_array($config['processors']) ? $config['processors'] : [];
        $normalised = [];

        foreach ($processors as $formHandle => $processorClasses) {
            if (!is_string($formHandle) || !is_array($processorClasses)) {
                continue;
            }

            foreach ($processorClasses as $processorClass) {
                if (!is_string($processorClass) || $processorClass === '') {
                    continue;
                }

                $normalised[$formHandle][] = $processorClass;
            }
        }

        return $normalised;
    }

    /** @param class-string<AdminFormProcessorInterface> $processorClass */
    private function instantiateProcessor(string $processorClass): AdminFormProcessorInterface
    {
        if (!class_exists($processorClass)) {
            throw new InvalidArgumentException('Admin form processor class does not exist: ' . $processorClass);
        }

        $processor = new $processorClass();
        if (!$processor instanceof AdminFormProcessorInterface) {
            throw new InvalidArgumentException('Admin form processor must implement AdminFormProcessorInterface: ' . $processorClass);
        }

        return $processor;
    }
}
