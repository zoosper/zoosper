<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Executes registered method plugins for a method invocation.
 */
final readonly class MethodPluginExecutor
{
    public function __construct(
        private MethodPluginRegistry $registry,
        private MethodPluginFactory $factory = new MethodPluginFactory(),
        private MethodInterceptorChain $chain = new MethodInterceptorChain(),
    ) {
    }

    /**
     * @param callable(MethodInvocation): mixed $final
     */
    public function execute(MethodInvocation $invocation, callable $final): mixed
    {
        $interceptors = [];

        foreach ($this->registry->for($invocation->subjectKey(), $invocation->method) as $definition) {
            $interceptors[] = $this->factory->create($definition);
        }

        return $this->chain->execute($invocation, $interceptors, $final);
    }
}
