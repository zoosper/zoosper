<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginExecutor;
use Zoosper\Core\Plugin\MethodPluginFileConfigLoader;
use Zoosper\Core\Plugin\MethodPluginRegistry;

final class Phase141SampleService
{
    public function render(string $value): string
    {
        return 'service:' . $value;
    }
}

final class Phase141PrefixPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        $arguments = $invocation->arguments;
        $arguments[0] = 'prefix-' . $arguments[0];

        return $next($invocation->withArguments($arguments)) . ':prefix';
    }
}

final class Phase141SuffixPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        return $next($invocation) . ':suffix';
    }
}

it('discovers plugin config files and executes plugins against a safe sample service', function (): void {
    $dir = sys_get_temp_dir() . '/zoosper-method-plugin-discovery-' . bin2hex(random_bytes(6));
    mkdir($dir, 0775, true);

    $configFile = $dir . '/plugins.php';
    file_put_contents($configFile, "<?php\nreturn ['plugins' => [\n    ['subject' => 'Phase141SampleService', 'method' => 'render', 'plugin' => 'Phase141SuffixPlugin', 'sortOrder' => 200],\n    ['subject' => 'Phase141SampleService', 'method' => 'render', 'plugin' => 'Phase141PrefixPlugin', 'sortOrder' => 10],\n]];\n");

    $definitions = (new MethodPluginFileConfigLoader())->loadFiles([$configFile]);
    $executor = new MethodPluginExecutor(new MethodPluginRegistry($definitions));
    $service = new Phase141SampleService();

    $result = $executor->execute(
        new MethodInvocation(Phase141SampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    expect($result)->toBe('service:prefix-value:suffix:prefix');
});

it('keeps method plugin discovery proof tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-method-plugin-sample-service.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-discovery.php')->toBeFile();
});
