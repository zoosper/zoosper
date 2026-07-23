<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\MethodInterceptorInterface;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginConfigValidator;
use Zoosper\Core\Plugin\MethodPluginDefinition;
use Zoosper\Core\Plugin\MethodPluginException;
use Zoosper\Core\Plugin\MethodPluginFactory;
use Zoosper\Core\Plugin\MethodPluginValidationIssue;
use Zoosper\Core\Plugin\MethodPluginValidationResult;

final class Phase141DiagnosticsTestValidPlugin implements MethodInterceptorInterface
{
    public function intercept(MethodInvocation $invocation, callable $next): mixed
    {
        return $next($invocation);
    }
}

final class Phase141DiagnosticsTestWrongPlugin
{
}

it('reports missing classes, wrong interfaces, and malformed entries', function (): void {
    $result = (new MethodPluginConfigValidator())->validateConfig([
        'plugins' => [
            ['subject' => 'Sample', 'method' => 'run', 'plugin' => Phase141DiagnosticsTestValidPlugin::class],
            ['subject' => 'Sample', 'method' => 'run', 'plugin' => 'MissingPluginClassForTest'],
            ['subject' => 'Sample', 'method' => 'run', 'plugin' => Phase141DiagnosticsTestWrongPlugin::class],
            ['subject' => 'Sample', 'plugin' => Phase141DiagnosticsTestValidPlugin::class],
        ],
    ]);

    expect($result)->toBeInstanceOf(MethodPluginValidationResult::class);
    expect($result->hasErrors())->toBeTrue();
    expect($result->issues)->toHaveCount(3);
    expect($result->issues[0])->toBeInstanceOf(MethodPluginValidationIssue::class);

    $messages = implode("\n", $result->messages());
    expect($messages)->toContain('Method plugin class does not exist: MissingPluginClassForTest');
    expect($messages)->toContain('Method plugin class must implement');
    expect($messages)->toContain('Method plugin config entry requires non-empty method.');
});

it('throws descriptive exceptions from the plugin factory path', function (): void {
    expect(fn (): mixed => (new MethodPluginFactory())->create(new MethodPluginDefinition('Sample', 'run', 'MissingPluginClassForTest')))
        ->toThrow(MethodPluginException::class, 'Method plugin class does not exist: MissingPluginClassForTest');
});

it('keeps method plugin diagnostics tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-method-plugin-diagnostics.php')->toBeFile();
    expect($root . '/tools/audit-method-plugin-diagnostics.php')->toBeFile();
});
