<?php

declare(strict_types=1);

use Zoosper\Admin\PageMomentum\PageMomentumCardsPresenter;
use Zoosper\Admin\PageMomentum\PageMomentumFacts;

it('formats facts into ordered display cards', function (): void {
    $facts = new PageMomentumFacts(
        totalPages: 5,
        publishedPages: 3,
        draftPages: 2,
        publishedLast7Days: 2,
        updatedLast7Days: 3,
        mostRecentTitle: 'Home',
        mostRecentUpdatedAt: '2026-07-24 10:00:00',
    );

    $cards = (new PageMomentumCardsPresenter($facts))->cards();

    expect($cards)->toHaveCount(6);

    $byKey = [];
    foreach ($cards as $card) {
        $byKey[$card['key']] = $card;
    }

    expect($byKey['total_pages']['value'])->toBe('5')
        ->and($byKey['published_pages']['value'])->toBe('3')
        ->and($byKey['published_pages']['hint'])->toContain('60%')
        ->and($byKey['draft_pages']['value'])->toBe('2')
        ->and($byKey['published_last_7_days']['value'])->toBe('2')
        ->and($byKey['updated_last_7_days']['value'])->toBe('3')
        ->and($byKey['most_recent']['value'])->toBe('Home')
        ->and($byKey['most_recent']['hint'])->toContain('2026-07-24 10:00:00');
});

it('shows friendly placeholders when there are no pages', function (): void {
    $facts = new PageMomentumFacts(
        totalPages: 0,
        publishedPages: 0,
        draftPages: 0,
        publishedLast7Days: 0,
        updatedLast7Days: 0,
        mostRecentTitle: null,
        mostRecentUpdatedAt: null,
    );

    $cards = (new PageMomentumCardsPresenter($facts))->cards();
    $byKey = [];
    foreach ($cards as $card) {
        $byKey[$card['key']] = $card;
    }

    expect($byKey['published_pages']['hint'])->toContain('0%')
        ->and($byKey['most_recent']['value'])->toBe('No pages yet')
        ->and($byKey['most_recent']['hint'])->toBe('Nothing has been updated');
});
