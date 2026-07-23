<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginConfigSource;
use Zoosper\Core\Plugin\MethodPluginConfigSourceDiscovery;
use Zoosper\Core\Plugin\MethodPluginExecutor;
use Zoosper\Core\Plugin\MethodPluginModuleConfigLoader;
use Zoosper\Core\Plugin\MethodPluginRegistry;

final class Phase141ModuleDiscoveryService
{
    public function render(string $value): string
    {
        return 'service:' . $value;
    }
}

final class Phase141ModulePrefixPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        $arguments = $invocation->arguments;
        $arguments[0] = 'module-' . $arguments[0];

        return $next($invocation->withArguments($arguments)) . ':module-prefix';
    }
}

final class Phase141ModuleSuffixPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        return $next($invocation) . ':module-suffix';
    }
}

it('discovers module config/plugins.php files and executes a safe sample service', function (): void {
    $base = sys_get_temp_dir() . '/zoosper-method-plugin-module-discovery-' . bin2hex(random_bytes(6));
    $moduleA = $base . '/module-a';
    $moduleB = $base . '/module-b';
    mkdir($moduleA . '/config', 0775, true);
    mkdir($moduleB . '/config', 0775, true);

    file_put_contents($moduleA . '/config/plugins.php', "<?php\nreturn ['plugins' => [\n    ['subject' => 'Phase141ModuleDiscoveryService', 'method' => 'render', 'plugin' => 'Phase141ModuleSuffixPlugin', 'sortOrder' => 200],\n]];\n");
    file_put_contents($moduleB . '/config/plugins.php', "<?php\nreturn ['plugins' => [\n    ['subject' => 'Phase141ModuleDiscoveryService', 'method' => 'render', 'plugin' => 'Phase141ModulePrefixPlugin', 'sortOrder' => 10],\n]];\n");

    $sources = (new MethodPluginConfigSourceDiscovery())->discover([
        'module-a' => $moduleA,
        'module-b' => $moduleB,
    ]);

    expect($sources)->toHaveCount(2);
    expect($sources[0])->toBeInstanceOf(MethodPluginConfigSource::class);

    $definitions = (new MethodPluginModuleConfigLoader())->load($sources);
    $executor = new MethodPluginExecutor(new MethodPluginRegistry($definitions));
    $service = new Phase141ModuleDiscoveryService();

    $result = $executor->execute(
        new MethodInvocation(Phase141ModuleDiscoveryService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    expect($result)->toBe('service:module-value:module-suffix:module-prefix');
});

it('keeps module discovery tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-method-plugin-module-discovery.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-module-discovery.php')->toBeFile();
});
