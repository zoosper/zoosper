<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginConfigSourceDiscovery;
use Zoosper\Core\Plugin\MethodPluginExecutor;
use Zoosper\Core\Plugin\MethodPluginModuleConfigLoader;
use Zoosper\Core\Plugin\MethodPluginRegistry;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

final class Phase141ToolModuleDiscoveryService
{
    public function render(string $value): string
    {
        return 'service:' . $value;
    }
}

final class Phase141ToolModulePrefixPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        $arguments = $invocation->arguments;
        $arguments[0] = 'module-' . $arguments[0];

        return $next($invocation->withArguments($arguments)) . ':module-prefix';
    }
}

final class Phase141ToolModuleSuffixPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        return $next($invocation) . ':module-suffix';
    }
}

$errors = 0;
$report = [];
$report[] = '## Method Plugin Module Discovery Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$tmp = sys_get_temp_dir() . '/zoosper-method-plugin-module-tool-' . bin2hex(random_bytes(6));
$moduleA = $tmp . '/module-a';
$moduleB = $tmp . '/module-b';
mkdir($moduleA . '/config', 0775, true);
mkdir($moduleB . '/config', 0775, true);

file_put_contents($moduleA . '/config/plugins.php', "<?php\nreturn ['plugins' => [\n    ['subject' => 'Phase141ToolModuleDiscoveryService', 'method' => 'render', 'plugin' => 'Phase141ToolModuleSuffixPlugin', 'sortOrder' => 200],\n]];\n");
file_put_contents($moduleB . '/config/plugins.php', "<?php\nreturn ['plugins' => [\n    ['subject' => 'Phase141ToolModuleDiscoveryService', 'method' => 'render', 'plugin' => 'Phase141ToolModulePrefixPlugin', 'sortOrder' => 10],\n]];\n");

try {
    $sources = (new MethodPluginConfigSourceDiscovery())->discover([
        'module-a' => $moduleA,
        'module-b' => $moduleB,
    ]);
    $definitions = (new MethodPluginModuleConfigLoader())->load($sources);
    $executor = new MethodPluginExecutor(new MethodPluginRegistry($definitions));
    $service = new Phase141ToolModuleDiscoveryService();

    $result = $executor->execute(
        new MethodInvocation(Phase141ToolModuleDiscoveryService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    $expected = 'service:module-value:module-suffix:module-prefix';
    $report[] = 'Sources discovered: ' . count($sources);
    $report[] = 'Definitions loaded: ' . count($definitions);
    $report[] = 'Result: ' . $result;
    $report[] = 'Expected: ' . $expected;
    $report[] = 'Module discovery proof: ' . ($result === $expected ? 'yes' : 'no');

    if ($result !== $expected || count($sources) !== 2 || count($definitions) !== 2) {
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

file_put_contents($reportDir . '/method-plugin-module-discovery-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-module-discovery-proof.log', "METHOD_PLUGIN_MODULE_DISCOVERY_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
