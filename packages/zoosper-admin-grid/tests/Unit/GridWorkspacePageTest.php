<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspaceCsrf;

test('workspace CSRF value requires both host-provided components', function (): void {
    $csrf = new GridWorkspaceCsrf('_csrf', 'token');

    expect($csrf->field)->toBe('_csrf');
    expect($csrf->token)->toBe('token');
    expect(fn () => new GridWorkspaceCsrf('', 'token'))
        ->toThrow(\InvalidArgumentException::class, 'must be non-empty');
    expect(fn () => new GridWorkspaceCsrf('_csrf', ''))
        ->toThrow(\InvalidArgumentException::class, 'must be non-empty');
});











