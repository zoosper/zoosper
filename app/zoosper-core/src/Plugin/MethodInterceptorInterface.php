<?php

declare(strict_types=1);

namespace Zoosper\Core\Plugin;

/**
 * Intercepts a method call and may decide whether/how to proceed.
 */
interface MethodInterceptorInterface
{
    /**
     * @param callable(MethodInvocation): mixed $next
     */
    public function intercept(MethodInvocation $invocation, callable $next): mixed;
}
