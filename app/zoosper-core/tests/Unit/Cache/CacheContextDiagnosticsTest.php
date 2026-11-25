<?php

declare(strict_types=1);

use Zoosper\Core\Cache\CacheContext;

it('exposes private fragment dimensions for diagnostics', function (): void {
    $context = new CacheContext('web', 'store', 'view', 'en', 'USD', 'default', 'example.com', '/page', true, 'members', 'page', 'a=1');

    expect($context->toArray())->toBe($context->privateFragmentDimensions())
        ->and($context->toArray()['auth'])->toBe('yes')
        ->and($context->toArray()['customer_group'])->toBe('members')
        ->and($context->toArray()['query'])->toBe('a=1');
});