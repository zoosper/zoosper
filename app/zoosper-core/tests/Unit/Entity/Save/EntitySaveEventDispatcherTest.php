<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Entity\Save;

/**
 * Regression tests that LOCK IN Phase 1.20 - Entity Save Lifecycle Events.
 *
 * Phase 1.21 - First regression suite (co-located in zoosper-core).
 *
 * These tests protect the lifecycle-event foundation delivered in Phase 1.20 so
 * that it cannot silently break when it is wired into the real Page / AdminUser
 * save flows in Phase 1.22. They assert, against the REAL dispatcher API:
 *   1. Listeners fire in the canonical lifecycle order.
 *   2. A listener can observe AND mutate the shared EntitySaveContext.
 *   3. An object listener implementing the interface is invoked.
 *
 * PCI-aware: never assert on or dump secrets/tokens in these tests.
 */

use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcher;
use Zoosper\Core\Entity\Save\EntitySaveEventListenerInterface;
use Zoosper\Core\Entity\Save\EntitySaveLifecycle;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;

/**
 * Build a minimal, valid save context for dispatcher tests.
 */
function makeContext(): EntitySaveContext
{
    return new EntitySaveContext(
        entityType: 'page',
        data: new EntityDataObject(),
        fieldRegistry: new FieldDefinitionRegistry(),
    );
}

test('lifecycle listeners fire in the canonical order', function () {
    // Arrange - the canonical lifecycle sequence, and a recorder per event.
    $events = [
        EntitySaveLifecycle::DATA_COLLECT_BEFORE,
        EntitySaveLifecycle::DATA_COLLECT_AFTER,
        EntitySaveLifecycle::VALIDATE_BEFORE,
        EntitySaveLifecycle::VALIDATE_AFTER,
        EntitySaveLifecycle::SAVE_BEFORE,
        EntitySaveLifecycle::SAVE_AFTER,
        EntitySaveLifecycle::COMMIT_AFTER,
    ];

    $order      = [];
    $dispatcher = new EntitySaveEventDispatcher();

    foreach ($events as $event) {
        $dispatcher->listen($event, function () use (&$order, $event): void {
            $order[] = $event;
        });
    }

    // Act - dispatch each event in the canonical order.
    foreach ($events as $event) {
        $dispatcher->dispatch($event, makeContext());
    }

    // Assert - toBe() does a strict (===) ordered array comparison: it checks
    // keys, values AND order, so it fully verifies the canonical sequence.
    expect($order)->toBe($events);
});

test('a listener can observe and mutate the context payload', function () {
    // Arrange - a listener that stamps data and records an error.
    $dispatcher = new EntitySaveEventDispatcher();
    $dispatcher->listen(EntitySaveLifecycle::SAVE_BEFORE, function (EntitySaveContext $c): void {
        $c->data()->setData('stamped_by', 'phase-1.20-listener');
        $c->addError('title', 'too short');
    });

    // Act
    $context  = makeContext();
    $returned = $dispatcher->dispatch(EntitySaveLifecycle::SAVE_BEFORE, $context);

    // Assert - same context is returned and the mutations are visible.
    expect($returned)->toBe($context);
    expect($context->data()->getData('stamped_by'))->toBe('phase-1.20-listener');
    expect($context->hasErrors())->toBeTrue();
    expect($context->errors())->toHaveKey('title');
});

test('an object listener implementing the interface is invoked', function () {
    // Arrange - an anonymous listener implementing the real interface.
    $listener = new class implements EntitySaveEventListenerInterface {
        public bool $handled = false;

        public function handle(EntitySaveContext $context): void
        {
            $this->handled = true;
        }
    };

    $dispatcher = new EntitySaveEventDispatcher();
    $dispatcher->listen(EntitySaveLifecycle::VALIDATE_AFTER, $listener);

    // Act
    $dispatcher->dispatch(EntitySaveLifecycle::VALIDATE_AFTER, makeContext());

    // Assert
    expect($listener->handled)->toBeTrue();
});
