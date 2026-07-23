<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginDefinition;
use Zoosper\Core\Plugin\MethodPluginExecutor;
use Zoosper\Core\Plugin\MethodPluginFactory;
use Zoosper\Core\Plugin\MethodPluginRegistry;
use Zoosper\Core\Plugin\MethodPluginResolverInterface;
use Zoosper\Core\Plugin\ReflectionMethodPluginResolver;

final class Phase141ResolverSampleService
{
    public function render(string $value): string
    {
        return 'service:' . $value;
    }
}

final class Phase141ResolverPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        $arguments = $invocation->arguments;
        $arguments[0] = 'resolved-' . $arguments[0];

        return $next($invocation->withArguments($arguments)) . ':resolved';
    }
}

final class Phase141RecordingResolver implements MethodPluginResolverInterface
{
    /** @var list<string> */
    public array $resolved = [];

    public function resolve(string $pluginClass): object
    {
        $this->resolved[] = $pluginClass;

        return new $pluginClass();
    }
}

it('creates plugins through the resolver seam and executes a safe sample service', function (): void {
    $resolver = new Phase141RecordingResolver();
    $factory = new MethodPluginFactory($resolver);
    $registry = new MethodPluginRegistry([
        new MethodPluginDefinition(
            subject: Phase141ResolverSampleService::class,
            method: 'render',
            pluginClass: Phase141ResolverPlugin::class,
            sortOrder: 10,
        ),
    ]);
    $executor = new MethodPluginExecutor($registry, $factory);
    $service = new Phase141ResolverSampleService();

    $result = $executor->execute(
        new MethodInvocation(Phase141ResolverSampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    expect($resolver->resolved)->toBe([Phase141ResolverPlugin::class]);
    expect($result)->toBe('service:resolved-value:resolved');
});

it('keeps the default reflection resolver available', function (): void {
    $resolver = new ReflectionMethodPluginResolver();

    expect($resolver->resolve(Phase141ResolverPlugin::class))->toBeInstanceOf(Phase141ResolverPlugin::class);
});

it('keeps resolver factory proof tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-method-plugin-resolver-factory.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-resolver-factory.php')->toBeFile();
});
