<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Lightweight adapter for tests and config-created callable interceptors.
 */
final readonly class CallableMethodInterceptor implements MethodInterceptorInterface
{
    /**
     * @param callable(MethodInvocation, callable): mixed $callback
     */
    public function __construct(
        private mixed $callback,
    ) {
    }

    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        return ($this->callback)($invocation, $next);
    }
}
