<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Page\Admin\PageGridLiveMarkupContract;

test('complete live Pages Grid markup satisfies the cutover contract', function (): void {
    $html = <<<'HTML'
<section data-grid-workspace>
<select name="site_id[]" multiple></select>
<select name="page_size" data-grid-page-size></select>
<div data-grid-view-status></div>
<div data-grid-view-actions></div>
<input name="visible_columns[]">
<input name="column_order[]">
<a data-grid-export href="/admin/pages/export">Export CSV</a>
</section>
HTML;

    expect(fn () => PageGridLiveMarkupContract::assertComplete($html))->not->toThrow(\Throwable::class);
});

test('legacy Pages markup is rejected with actionable missing markers', function (): void {
    $legacy = '<form class="grid-filters"><span>Site ID</span><input type="text" name="site_id">'
        . '<input type="hidden" name="page_size" value="20"></form><table class="grid-table"></table>';

    expect(fn () => PageGridLiveMarkupContract::assertComplete($legacy))
        ->toThrow(\RuntimeException::class, 'Pages Grid live cutover is incomplete');
});










