<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginConfigValidator;
use Zoosper\Core\Plugin\MethodPluginDefinition;
use Zoosper\Core\Plugin\MethodPluginFactory;

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

final class Phase141DiagnosticsValidPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        return $next($invocation);
    }
}

final class Phase141DiagnosticsWrongPlugin
{
}

$errors = 0;
$report = [];
$report[] = '## Method Plugin Diagnostics Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';

$validator = new MethodPluginConfigValidator();
$result = $validator->validateConfig([
    'plugins' => [
        ['subject' => 'Sample', 'method' => 'run', 'plugin' => Phase141DiagnosticsValidPlugin::class],
        ['subject' => 'Sample', 'method' => 'run', 'plugin' => 'MissingPluginClassForDiagnostics'],
        ['subject' => 'Sample', 'method' => 'run', 'plugin' => Phase141DiagnosticsWrongPlugin::class],
        ['subject' => 'Sample', 'plugin' => Phase141DiagnosticsValidPlugin::class],
    ],
]);

$messages = $result->messages();
$report[] = 'Issues found: ' . count($messages);
foreach ($messages as $message) {
    $report[] = '- ' . $message;
}

$expectedFragments = [
    'Method plugin class does not exist: MissingPluginClassForDiagnostics',
    'Method plugin class must implement',
    'Method plugin config entry requires non-empty method.',
];

foreach ($expectedFragments as $fragment) {
    $matched = false;
    foreach ($messages as $message) {
        if (str_contains($message, $fragment)) {
            $matched = true;
            break;
        }
    }

    if (!$matched) {
        $report[] = 'Missing expected diagnostic fragment: ' . $fragment;
        $errors++;
    }
}

try {
    (new MethodPluginFactory())->create(new MethodPluginDefinition('Sample', 'run', 'MissingPluginClassForDiagnostics'));
    $report[] = 'Missing-class factory diagnostic: no exception';
    $errors++;
} catch (Throwable $exception) {
    $report[] = 'Missing-class factory diagnostic: ' . $exception->getMessage();
}

$report[] = 'Diagnostics proof: ' . ($errors === 0 ? 'yes' : 'no');
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}
file_put_contents($reportDir . '/method-plugin-diagnostics-proof.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/method-plugin-diagnostics-proof.log', "METHOD_PLUGIN_DIAGNOSTICS_PROOF_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
