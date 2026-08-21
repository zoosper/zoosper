<?php

declare(strict_types=1);

use Zoosper\Pagination\Pager;

/**
 * PERFORMANCE/COST-AMPLIFICATION REGRESSION TEST — proves
 * Pager::fromQuery() now clamps an excessively large ?page= value to a
 * fixed safety ceiling, so it can never produce an arbitrarily huge
 * OFFSET for the database to scan through and discard.
 *
 * File placement: packages/zoosper-pagination/tests/Unit/PagerMaxPageClampTest.php.
 */
it('clamps an excessively large page number to the default max page ceiling', function (): void {
    $pager = Pager::fromQuery(['page' => '999999999']);

    expect($pager->page)->toBe(100_000);
    // Confirms the resulting OFFSET is genuinely bounded — the actual
    // cost-amplification concern this fix addresses.
    expect($pager->offset())->toBe((100_000 - 1) * 20);
});

it('respects a custom, explicitly-configured max page value', function (): void {
    $pager = Pager::fromQuery(['page' => '50000'], maxPage: 100);

    expect($pager->page)->toBe(100);
});

it('leaves a normal, legitimate page number completely untouched', function (): void {
    $pager = Pager::fromQuery(['page' => '5']);

    expect($pager->page)->toBe(5);
    expect($pager->offset())->toBe(4 * 20);
});

it('still enforces the existing minimum page of 1 for zero/negative input (no regression)', function (): void {
    expect(Pager::fromQuery(['page' => '0'])->page)->toBe(1);
    expect(Pager::fromQuery(['page' => '-5'])->page)->toBe(1);
});

it('defaults to page 1 when no page query parameter is provided (no regression)', function (): void {
    expect(Pager::fromQuery([])->page)->toBe(1);
});

it('still correctly clamps page_size independently of the new page ceiling (no regression)', function (): void {
    $pager = Pager::fromQuery(['page' => '2', 'page_size' => '99999'], maxPageSize: 100);

    expect($pager->pageSize)->toBe(100);
    expect($pager->page)->toBe(2);
});

it('computes a correct, bounded offset at the exact max page boundary', function (): void {
    $pager = Pager::fromQuery(['page' => (string) 100_000], maxPage: 100_000);

    expect($pager->page)->toBe(100_000);
    expect($pager->offset())->toBe(99_999 * 20);
});
