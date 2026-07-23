<?php

declare(strict_types=1);

use Zoosper\Core\Plugin\CallableMethodInterceptor;
use Zoosper\Core\Plugin\MethodInterceptorChain;
use Zoosper\Core\Plugin\MethodInvocation;
use Zoosper\Core\Plugin\MethodPluginConfigLoader;
use Zoosper\Core\Plugin\MethodPluginDefinition;
use Zoosper\Core\Plugin\MethodPluginRegistry;

it('executes method interceptors around a final callable in sort order', function (): void {
    $chain = new MethodInterceptorChain();
    $log = [];

    $first = new CallableMethodInterceptor(function (MethodInvocation $invocation, callable $next) use (&$log): mixed {
        $log[] = 'first:before';
        $result = $next($invocation->withArguments(['value' => 'changed']));
        $log[] = 'first:after';

        return $result . ':first';
    });

    $second = new CallableMethodInterceptor(function (MethodInvocation $invocation, callable $next) use (&$log): mixed {
        $log[] = 'second:before:' . $invocation->arguments['value'];
        $result = $next($invocation);
        $log[] = 'second:after';

        return $result . ':second';
    });

    $result = $chain->execute(
        new MethodInvocation('SampleService', 'save', ['value' => 'original']),
        [$first, $second],
        function (MethodInvocation $invocation) use (&$log): string {
            $log[] = 'final:' . $invocation->arguments['value'];

            return 'done';
        }
    );

    expect($result)->toBe('done:second:first');
    expect($log)->toBe([
        'first:before',
        'second:before:changed',
        'final:changed',
        'second:after',
        'first:after',
    ]);
});

it('loads and orders method plugin definitions from array config', function (): void {
    $loader = new MethodPluginConfigLoader();

    $definitions = $loader->load([
        'plugins' => [
            ['subject' => 'SampleService', 'method' => 'save', 'plugin' => 'SecondPlugin', 'sortOrder' => 200],
            ['subject' => 'SampleService', 'method' => 'save', 'plugin' => 'FirstPlugin', 'sortOrder' => 10],
            ['subject' => 'SampleService', 'method' => 'save', 'plugin' => 'DisabledPlugin', 'enabled' => false],
        ],
    ]);

    $registry = new MethodPluginRegistry($definitions);
    $plugins = $registry->for('SampleService', 'save');

    expect($plugins)->toHaveCount(2);
    expect($plugins[0])->toBeInstanceOf(MethodPluginDefinition::class);
    expect($plugins[0]->pluginClass)->toBe('FirstPlugin');
    expect($plugins[1]->pluginClass)->toBe('SecondPlugin');
});

it('keeps method plugin foundation audit tooling available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-method-plugin-foundation.php')->toBeFile();
});
