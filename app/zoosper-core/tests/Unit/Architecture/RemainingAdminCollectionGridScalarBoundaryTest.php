<?php

declare(strict_types=1);

use Zoosper\Auth\Admin\Grid\AccessToken\AccessTokenGrid;
use Zoosper\Menu\Admin\Grid\MenuGrid;
use Zoosper\Site\Admin\Grid\SiteDomainGrid;
use Zoosper\Site\Admin\Grid\SiteGrid;

it('normalises remaining Admin collection Grid filters without casting non-scalars', function (): void {
    foreach ([AccessTokenGrid::class, MenuGrid::class, SiteDomainGrid::class, SiteGrid::class] as $class) {
        $method = new ReflectionMethod($class, 'scalarFilter');
        expect($method->invoke(null, ' value '))->toBe(' value ')
            ->and($method->invoke(null, 42))->toBe('42')
            ->and($method->invoke(null, true))->toBe('1')
            ->and($method->invoke(null, false))->toBe('')
            ->and($method->invoke(null, null))->toBe('')
            ->and($method->invoke(null, ['unexpected']))->toBe('')
            ->and($method->invoke(null, new stdClass()))->toBe('');
    }
});
