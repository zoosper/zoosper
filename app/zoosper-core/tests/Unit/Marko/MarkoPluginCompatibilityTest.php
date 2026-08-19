<?php
declare(strict_types=1);
namespace Zoosper\Core\Tests\Unit\Marko;
use Marko\Core\Attributes\After;
use Marko\Core\Attributes\Before;
use Marko\Core\Attributes\Plugin;
use Marko\Core\Container\Container;
use Marko\Core\Container\PreferenceRegistry;
use Marko\Core\Plugin\InterceptorClassGenerator;
use Marko\Core\Plugin\PluginDiscovery;
use Marko\Core\Plugin\PluginInterceptedInterface;
use Marko\Core\Plugin\PluginInterceptor;
use Marko\Core\Plugin\PluginRegistry;
interface CompatibilityGreetingInterface { public function greet(string $name): string; }
final class CompatibilityGreeting implements CompatibilityGreetingInterface { public function greet(string $name): string { return 'Hello '.$name; } }
#[Plugin(target: CompatibilityGreetingInterface::class)]
final class CompatibilityGreetingPlugin
{
    #[Before]
    public function greet(string $name): array { return [strtoupper($name)]; }
    #[After(method: 'greet')]
    public function greetResult(string $result, string $name): string { return $result.'!'; }
}
it('proves installed Marko before and after plugins through normal container resolution', function (): void {
    $preferences = new PreferenceRegistry();
    $registry = new PluginRegistry();
    $registry->register((new PluginDiscovery())->parsePluginClass(CompatibilityGreetingPlugin::class));
    $container = new Container($preferences);
    $interceptor = new PluginInterceptor($container, $registry, new InterceptorClassGenerator());
    $container->setPluginInterceptor($interceptor);
    $container->instance(CompatibilityGreetingPlugin::class, new CompatibilityGreetingPlugin());
    $container->bind(CompatibilityGreetingInterface::class, CompatibilityGreeting::class);
    $service = $container->get(CompatibilityGreetingInterface::class);
    expect($service)->toBeInstanceOf(CompatibilityGreetingInterface::class)
        ->and($service)->toBeInstanceOf(PluginInterceptedInterface::class)
        ->and($service->greet('zoosper'))->toBe('Hello ZOOSPER!');
});
it('returns the real service when no Marko plugin applies', function (): void {
    $registry = new PluginRegistry();
    $container = new Container(new PreferenceRegistry());
    $container->setPluginInterceptor(new PluginInterceptor($container, $registry, new InterceptorClassGenerator()));
    $container->bind(CompatibilityGreetingInterface::class, CompatibilityGreeting::class);
    $service = $container->get(CompatibilityGreetingInterface::class);
    expect($service)->toBeInstanceOf(CompatibilityGreeting::class)
        ->and($service)->not->toBeInstanceOf(PluginInterceptedInterface::class);
});
