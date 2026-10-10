<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;

test('it decorates registered instances and factories without invoking an absent factory', function (): void {
    $services = new ServiceContainer();
    $instance = new stdClass();
    $services->set('instance', $instance);
    $services->decorate('instance', static function (ServiceContainer $container, object $inner) use ($services, $instance): object {
        expect($container)->toBe($services)
            ->and($inner)->toBe($instance);

        return (object) ['kind' => 'decorated-instance'];
    });

    $factoryCalls = 0;
    $services->factory('factory', static function () use (&$factoryCalls): object {
        ++$factoryCalls;

        return (object) ['kind' => 'inner-factory'];
    });
    $services->decorate('factory', static function (ServiceContainer $container, object $inner) use ($services): object {
        expect($container)->toBe($services)
            ->and($inner->kind)->toBe('inner-factory');

        return (object) ['kind' => 'decorated-factory'];
    });

    expect($services->get('instance')->kind)->toBe('decorated-instance')
        ->and($services->get('factory')->kind)->toBe('decorated-factory')
        ->and($factoryCalls)->toBe(1)
        ->and($services->get('factory')->kind)->toBe('decorated-factory')
        ->and($factoryCalls)->toBe(1);
});

test('it instantiates constructor-free classes through the validated reflection boundary', function (): void {
    $services = new ServiceContainer();

    expect($services->get(ServiceContainerCorrectnessFixture::class))
        ->toBeInstanceOf(ServiceContainerCorrectnessFixture::class)
        ->toBe($services->get(ServiceContainerCorrectnessFixture::class));
});

final class ServiceContainerCorrectnessFixture
{
}
