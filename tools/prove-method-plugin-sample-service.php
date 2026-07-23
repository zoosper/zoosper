<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginExecutor;
use Zoosper\Core\Plugin\MethodPluginFileConfigLoader;
use Zoosper\Core\Plugin\MethodPluginRegistry;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

final class Phase141ToolSampleService
{
    public function render(string $value): string
    {
        return 'service:' . $value;
    }
}

final class Phase141ToolPrefixPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        $arguments = $invocation->arguments;
        $arguments[0] = 'prefix-' . $arguments[0];

        return $next($invocation->withArguments($arguments)) . ':prefix';
    }
}

final class Phase141ToolSuffixPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        return $next($invocation) . ':suffix';
    }
}

$errors = 0;
$report = [];
$report[] = '## Method Plugin Sample Service Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$tmp = sys_get_temp_dir() . '/zoosper-method-plugin-tool-' . bin2hex(random_bytes(6));
mkdir($tmp, 0775, true);
$configFile = $tmp . '/plugins.php';

file_put_contents($configFile, "<?php\nreturn ['plugins' => [\n    ['subject' => 'Phase141ToolSampleService', 'method' => 'render', 'plugin' => 'Phase141ToolSuffixPlugin', 'sortOrder' => 200],\n    ['subject' => 'Phase141ToolSampleService', 'method' => 'render', 'plugin' => 'Phase141ToolPrefixPlugin', 'sortOrder' => 10],\n]];\n");

try {
    $definitions = (new MethodPluginFileConfigLoader())->loadFiles([$configFile]);
    $executor = new MethodPluginExecutor(new MethodPluginRegistry($definitions));
    $service = new Phase141ToolSampleService();

    $result = $executor->execute(
        new MethodInvocation(Phase141ToolSampleService::class, 'render', ['value']),
        static fn (MethodInvocation $invocation): string => $service->render($invocation->arguments[0])
    );

    $expected = 'service:prefix-value:suffix:prefix';
    $report[] = 'Definitions loaded: ' . count($definitions);
    $report[] = 'Result: ' . $result;
    $report[] = 'Expected: ' . $expected;
    $report[] = 'Sample service proof: ' . ($result === $expected ? 'yes' : 'no');

    if ($result !== $expected) {
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

file_put_contents($reportDir . '/method-plugin-sample-service-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-sample-service-proof.log', "METHOD_PLUGIN_SAMPLE_SERVICE_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
