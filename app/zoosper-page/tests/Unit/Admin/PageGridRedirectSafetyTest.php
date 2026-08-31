<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Page\Admin\PageGridWorkspace;

test('Pages Grid mutations always return to the fixed local Pages path', function (): void {
    expect(PageGridWorkspace::ACTION)->toBe('/admin/pages');
    expect(PageGridWorkspace::ACTION)->toStartWith('/');
    expect(PageGridWorkspace::ACTION)->not->toContain('://');
});










