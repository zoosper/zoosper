<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginDefinition;
use Zoosper\Core\Plugin\MethodPluginExecutor;
use Zoosper\Core\Plugin\MethodPluginFactory;
use Zoosper\Core\Plugin\MethodPluginRegistry;
use Zoosper\Core\Plugin\MethodPluginResolverInterface;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

final class Phase141ToolResolverSampleService
{
    public function render(string $value): string
    {
        return 'service:' . $value;
    }
}

final class Phase141ToolResolverPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        $arguments = $invocation->arguments;
        $arguments[0] = 'resolved-' . $arguments[0];

        return $next($invocation->withArguments($arguments)) . ':resolved';
    }
}

final class Phase141ToolRecordingResolver implements MethodPluginResolverInterface
{
    /** @var list<string> */
    public array $resolved = [];

    public function resolve(string $pluginClass): object
    {
        $this->resolved[] = $pluginClass;

        return new $pluginClass();
    }
}

$errors = 0;
$report = [];
$report[] = '## Method Plugin Resolver Factory Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

try {
    $resolver = new Phase141ToolRecordingResolver();
    $factory = new MethodPluginFactory($resolver);
    $registry = new MethodPluginRegistry([
        new MethodPluginDefinition(
            subject: Phase141ToolResolverSampleService::class,
            method: 'render',
            pluginClass: Phase141ToolResolverPlugin::class,
            sortOrder: 10,
        ),
    ]);
    $executor = new MethodPluginExecutor($registry, $factory);
    $service = new Phase141ToolResolverSampleService();

    $result = $executor->execute(
        new MethodInvocation(Phase141ToolResolverSampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    $expected = 'service:resolved-value:resolved';
    $report[] = 'Resolved classes: ' . implode(', ', $resolver->resolved);
    $report[] = 'Result: ' . $result;
    $report[] = 'Expected: ' . $expected;
    $report[] = 'Resolver factory proof: ' . ($result === $expected ? 'yes' : 'no');

    if ($result !== $expected || $resolver->resolved !== [Phase141ToolResolverPlugin::class]) {
        $errors++;
    }
} catch (Throwable $exception) {
    $report[] = 'Exception: ' . $exception->getMessage();
    $errors++;
}

$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/method-plugin-resolver-factory-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-resolver-factory-proof.log', "METHOD_PLUGIN_RESOLVER_FACTORY_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
