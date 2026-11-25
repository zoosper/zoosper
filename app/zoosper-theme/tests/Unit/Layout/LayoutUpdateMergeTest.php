<?php

declare(strict_types=1);

use Zoosper\Theme\Layout\LayoutUpdate;

it('skips non-array updates and merges layout directives in order', function (): void {
    $update = LayoutUpdate::merge([
        null,
        'invalid',
        ['remove' => ['old', 'old'], 'replace' => ['page' => 'first'], 'inject' => ['head' => ['one']]],
        ['remove' => ['other'], 'replace' => ['page' => 'second'], 'inject' => ['head' => 'two']],
    ]);

    expect($update->isRemoved('old'))->toBeTrue()
        ->and($update->isRemoved('other'))->toBeTrue()
        ->and($update->isRemoved('missing'))->toBeFalse()
        ->and($update->replacementFor('page'))->toBe('second')
        ->and($update->replacementFor('missing'))->toBeNull()
        ->and($update->injectionsFor('head'))->toBe(['one', 'two'])
        ->and($update->injectionsFor('missing'))->toBe([]);
});