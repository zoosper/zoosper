<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('Pages page builder uses one resolved state for query and rendering', function (): void {
    $source = (string) file_get_contents(
        dirname(__DIR__, 5) . '/app/zoosper-page/src/Admin/PageGridPageBuilder.php',
    );

    expect($source)->toContain('$state = $resolved[\'state\']')
        ->toContain('$this->dataSource->paginate($state->criteria)')
        ->toContain('state: $state')
        ->not->toContain('$_GET')
        ->not->toContain('$_POST');
});
