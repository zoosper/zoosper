<?php

declare(strict_types=1);

use Zoosper\Admin\PageMomentum\PageMomentumCardsRenderer;
use Zoosper\Admin\PageMomentum\PageMomentumFacts;

it('renders the real cards partial to html with the expected numbers', function (): void {
    $facts = new PageMomentumFacts(
        totalPages: 5,
        publishedPages: 3,
        draftPages: 2,
        publishedLast7Days: 2,
        updatedLast7Days: 3,
        mostRecentTitle: 'Home',
        mostRecentUpdatedAt: '2026-07-24 10:00:00',
    );

    $html = (new PageMomentumCardsRenderer())->render($facts);

    expect($html)->toContain('page-momentum-cards')
        ->and($html)->toContain('Total pages')
        ->and($html)->toContain('>5<')
        ->and($html)->toContain('Published')
        ->and($html)->toContain('60% of all pages are live')
        ->and($html)->toContain('Drafts')
        ->and($html)->toContain('Most recent update')
        ->and($html)->toContain('Home')
        ->and($html)->toContain('2026-07-24 10:00:00')
        ->and($html)->toContain('data-card="most_recent"');
});

it('escapes html in rendered card values', function (): void {
    $facts = new PageMomentumFacts(
        totalPages: 1,
        publishedPages: 1,
        draftPages: 0,
        publishedLast7Days: 0,
        updatedLast7Days: 1,
        mostRecentTitle: '<script>alert(1)</script>',
        mostRecentUpdatedAt: '2026-07-25 00:00:00',
    );

    $html = (new PageMomentumCardsRenderer())->render($facts);

    // The raw script tag must NOT appear; its escaped form must.
    expect($html)->not->toContain('<script>alert(1)</script>')
        ->and($html)->toContain('&lt;script&gt;');
});

it('shows the empty state when no cards are provided', function (): void {
    $html = (new PageMomentumCardsRenderer())->renderCards([]);

    expect($html)->toContain('No page momentum data available yet.');
});
