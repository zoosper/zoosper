<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Save;

/**
 * Regression tests for PageSaveValidationListener.
 *
 * Phase 1.25 - real page-save validation via the entity save lifecycle.
 * Verifies the rules (title required + min length, site required) and that the
 * listener is inert for non-page entities.
 *
 * PCI-aware: no secrets/tokens are used in these tests.
 */

use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;
use Zoosper\Page\Save\PageSaveValidationListener;

/**
 * Build a 'page' save context from a submitted form array.
 *
 * @param array<string, mixed> $form
 */
function makePageContext(array $form): EntitySaveContext
{
    $data = (new EntityDataObject())->addData($form);

    return new EntitySaveContext('page', $data, new FieldDefinitionRegistry());
}

test('valid page data passes', function () {
    // Arrange
    $context = makePageContext(['title' => 'Hello World', 'site_id' => '2']);

    // Act
    (new PageSaveValidationListener())->handle($context);

    // Assert
    expect($context->hasErrors())->toBeFalse();
});

test('empty title is rejected', function () {
    $context = makePageContext(['title' => '', 'site_id' => '1']);

    (new PageSaveValidationListener())->handle($context);

    expect($context->hasErrors())->toBeTrue();
    expect($context->errors())->toHaveKey('title');
});

test('short title is rejected', function () {
    $context = makePageContext(['title' => 'ab', 'site_id' => '1']);

    (new PageSaveValidationListener())->handle($context);

    expect($context->hasErrors())->toBeTrue();
    expect($context->errors())->toHaveKey('title');
});

test('missing site is rejected', function () {
    $context = makePageContext(['title' => 'Valid title', 'site_id' => '0']);

    (new PageSaveValidationListener())->handle($context);

    expect($context->hasErrors())->toBeTrue();
    expect($context->errors())->toHaveKey('site_id');
});

test('listener is inert for non-page entities', function () {
    // Arrange - an admin_user context with intentionally empty data.
    $context = new EntitySaveContext('admin_user', new EntityDataObject(), new FieldDefinitionRegistry());

    // Act
    (new PageSaveValidationListener())->handle($context);

    // Assert - the page listener adds no errors to a non-page save.
    expect($context->hasErrors())->toBeFalse();
});
