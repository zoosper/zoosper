<?php

declare(strict_types=1);

use Zoosper\Grid\BulkAction\GridBulkActor;
use Zoosper\Grid\BulkAction\GridBulkExecutionContext;

it('carries the authenticated actor to a feature executor', function (): void {
    $context = new GridBulkExecutionContext(new GridBulkActor(17, 'admin@example.test'));
    expect($context->actor->adminUserId)->toBe(17)
        ->and($context->actor->email)->toBe('admin@example.test');
});

it('rejects invalid actor identities', function (): void {
    expect(fn () => new GridBulkActor(0))->toThrow(InvalidArgumentException::class, 'positive');
    expect(fn () => new GridBulkActor(1, ''))->toThrow(InvalidArgumentException::class, 'email');
});
