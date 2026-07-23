<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Executes method interceptors in deterministic order around a final callable.
 */
final class MethodInterceptorChain
{
    /**
     * @param list<MethodInterceptorInterface> $interceptors
     * @param callable(MethodInvocation): mixed $final
     */
    public function execute(MethodInvocation $invocation, array $interceptors, callable $final): mixed
    {
        $next = array_reduce(
            array_reverse($interceptors),
            static fn (callable $next, MethodInterceptorInterface $interceptor): callable =>
                static fn (MethodInvocation $current): mixed => $interceptor->intercept($current, $next),
            $final
        );

        return $next($invocation);
    }
}
