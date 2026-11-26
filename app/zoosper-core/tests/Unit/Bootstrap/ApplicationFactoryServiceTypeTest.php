<?php

declare(strict_types=1);

use Zoosper\Core\Bootstrap\ApplicationFactory;
use Zoosper\Core\Container\ServiceContainer;

test('bootstrap service lookup validates registered instances and factories', function (): void {
    $services = new ServiceContainer();
    $lookup = new ReflectionMethod(ApplicationFactory::class, 'getTypedService');
    $expected = new ApplicationFactoryServiceTypeFixture();
    $services->set(ApplicationFactoryServiceTypeFixture::class, $expected);

    expect($lookup->invoke(null, $services, ApplicationFactoryServiceTypeFixture::class))->toBe($expected);

    $services->set(ApplicationFactoryServiceTypeContract::class, $expected);
    expect($lookup->invoke(null, $services, ApplicationFactoryServiceTypeContract::class))->toBe($expected);

    $services->factory(ApplicationFactoryServiceTypeFixture::class, static fn (): object => new stdClass());
    expect(fn () => $lookup->invoke(null, $services, ApplicationFactoryServiceTypeFixture::class))
        ->toThrow(TypeError::class);
});

interface ApplicationFactoryServiceTypeContract
{
}

final class ApplicationFactoryServiceTypeFixture implements ApplicationFactoryServiceTypeContract
{
}