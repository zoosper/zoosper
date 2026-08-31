<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

test('workspace styles include responsive focus and reduced-motion treatment', function (): void {
    $css = (string) file_get_contents(
        dirname(__DIR__, 2) . '/resources/admin/css/grid-workspace.css',
    );

    expect($css)->toContain(':focus-within')
        ->toContain('@media (max-width:48rem)')
        ->toContain('@media (prefers-reduced-motion:reduce)')
        ->toContain('.is-dragging');
});











