<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Tests\Unit;

use Zoosper\ApiGrid\Transport\ResponseBodyBuffer;

test('response buffer counts bytes across chunks and rejects an overflowing chunk', function (): void {
    $buffer = new ResponseBodyBuffer(4);

    expect($buffer->append('ab'))->toBe(2)
        ->and($buffer->append('é'))->toBe(2)
        ->and($buffer->body())->toBe('abé')
        ->and($buffer->isTooLarge())->toBeFalse()
        ->and($buffer->append('x'))->toBe(0)
        ->and($buffer->isTooLarge())->toBeTrue()
        ->and($buffer->body())->toBe('abé');
});