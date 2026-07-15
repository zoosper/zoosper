<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Entity\Save;

/**
 * Regression tests locking in the EntitySaveLifecycleRunner integration contract.
 *
 * Phase 1.23 - the orchestration that admin controllers will delegate their
 * saves to. These tests protect the exact behaviour before we wire the real
 * Page / AdminUser controllers into the runner:
 *   1. All 7 stages fire in order, with the save callback between SAVE_BEFORE
 *      and SAVE_AFTER.
 *   2. A listener that adds an error during validation aborts before persistence.
 *   3. A listener that adds an error at SAVE_BEFORE aborts before persistence.
 *   4. On the happy path the save callback runs exactly once.
 *
 * NOTE: this helper is named makeRunnerContext() (not makeContext()) because
 * co-located test files that share a namespace also share file-scope function
 * declarations - EntitySaveEventDispatcherTest.php already declares makeContext()
 * in this same namespace, so per-file helpers must have unique names.
 *
 * PCI-aware: never assert on or dump secrets/tokens here.
 */

use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcher;
use Zoosper\Core\Entity\Save\EntitySaveLifecycle;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;

/**
 * Build a minimal, valid save context for runner tests.
 */
function makeRunnerContext(): EntitySaveContext
{
    return new EntitySaveContext(
        entityType: 'page',
        data: new EntityDataObject(),
        fieldRegistry: new FieldDefinitionRegistry(),
    );
}

test('runs all stages in order with the save callback between SAVE_BEFORE and SAVE_AFTER', function () {
    // Arrange - record every lifecycle stage plus the callback position.
    $order      = [];
    $dispatcher = new EntitySaveEventDispatcher();

    foreach ([
        EntitySaveLifecycle::DATA_COLLECT_BEFORE,
        EntitySaveLifecycle::DATA_COLLECT_AFTER,
        EntitySaveLifecycle::VALIDATE_BEFORE,
        EntitySaveLifecycle::VALIDATE_AFTER,
        EntitySaveLifecycle::SAVE_BEFORE,
        EntitySaveLifecycle::SAVE_AFTER,
        EntitySaveLifecycle::COMMIT_AFTER,
    ] as $event) {
        $dispatcher->listen($event, function () use (&$order, $event): void {
            $order[] = $event;
        });
    }

    $runner = new EntitySaveLifecycleRunner($dispatcher);
    $ran    = 0;

    // Act
    $runner->run(makeRunnerContext(), function () use (&$order, &$ran): void {
        $order[] = 'SAVE_CALLBACK';
        $ran++;
    });

    // Assert - full ordered sequence with the callback in the right place.
    expect($order)->toBe([
        EntitySaveLifecycle::DATA_COLLECT_BEFORE,
        EntitySaveLifecycle::DATA_COLLECT_AFTER,
        EntitySaveLifecycle::VALIDATE_BEFORE,
        EntitySaveLifecycle::VALIDATE_AFTER,
        EntitySaveLifecycle::SAVE_BEFORE,
        'SAVE_CALLBACK',
        EntitySaveLifecycle::SAVE_AFTER,
        EntitySaveLifecycle::COMMIT_AFTER,
    ]);
    expect($ran)->toBe(1);
});

test('aborts before persistence when a listener adds an error during validation', function () {
    // Arrange - a validation listener that rejects the save.
    $dispatcher = new EntitySaveEventDispatcher();
    $dispatcher->listen(EntitySaveLifecycle::VALIDATE_BEFORE, function (EntitySaveContext $c): void {
        $c->addError('title', 'required');
    });

    $runner  = new EntitySaveLifecycleRunner($dispatcher);
    $ran     = 0;
    $context = makeRunnerContext();

    // Act
    $returned = $runner->run($context, function () use (&$ran): void {
        $ran++;
    });

    // Assert - the save callback never ran, and the context carries the error.
    expect($ran)->toBe(0);
    expect($context->hasErrors())->toBeTrue();
    expect($returned)->toBe($context);
});

test('aborts before persistence when a listener adds an error at SAVE_BEFORE', function () {
    // Arrange - a listener that blocks right before persistence.
    $dispatcher = new EntitySaveEventDispatcher();
    $dispatcher->listen(EntitySaveLifecycle::SAVE_BEFORE, function (EntitySaveContext $c): void {
        $c->addError('slug', 'already taken');
    });

    $runner  = new EntitySaveLifecycleRunner($dispatcher);
    $ran     = 0;
    $context = makeRunnerContext();

    // Act
    $runner->run($context, function () use (&$ran): void {
        $ran++;
    });

    // Assert
    expect($ran)->toBe(0);
    expect($context->hasErrors())->toBeTrue();
});

test('save callback runs exactly once on the happy path', function () {
    // Arrange - no listeners at all.
    $runner  = new EntitySaveLifecycleRunner(new EntitySaveEventDispatcher());
    $ran     = 0;
    $context = makeRunnerContext();

    // Act
    $runner->run($context, function () use (&$ran): void {
        $ran++;
    });

    // Assert
    expect($ran)->toBe(1);
    expect($context->hasErrors())->toBeFalse();
});
