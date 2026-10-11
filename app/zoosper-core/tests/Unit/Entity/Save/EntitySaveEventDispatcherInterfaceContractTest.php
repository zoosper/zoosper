<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Entity\Save;

use Zoosper\Core\Entity\Save\EntitySaveEventDispatcher;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcherInterface;
use Zoosper\Core\Entity\Save\EntitySaveLifecycle;
use Zoosper\Core\Entity\Save\ModuleEntitySaveListenerLoader;
use Zoosper\Core\Container\ServiceContainer;

it('exposes listener registration through the entity-save dispatcher interface', function (): void {
    $dispatcher = new EntitySaveEventDispatcher();
    expect($dispatcher)->toBeInstanceOf(EntitySaveEventDispatcherInterface::class);

    $loaderReflection = new \ReflectionClass(ModuleEntitySaveListenerLoader::class);
    $loader = $loaderReflection->newInstanceWithoutConstructor();
    $loaderReflection->getProperty('services')->setValue($loader, new ServiceContainer());

    $called = false;
    $loader->attachFromConfig($dispatcher, [
        EntitySaveLifecycle::VALIDATE_AFTER => [static function () use (&$called): void { $called = true; }],
    ]);
    $dispatcher->dispatch(EntitySaveLifecycle::VALIDATE_AFTER, makeContext());

    expect($called)->toBeTrue();
});