<?php

declare(strict_types=1);

namespace Zoosper\Admin\PageMomentum;

/**
 * Renders the page momentum cards partial to an HTML string.
 *
 * This is a thin convenience so a controller (or a test) can obtain the cards
 * HTML without wiring a full template engine. It simply provides the $cards
 * variable expected by the partial and captures its output.
 */
final class PageMomentumCardsRenderer
{
    /**
     * @param string|null $partialPath Absolute path to the cards partial. When
     *                                 null, the bundled partial is used.
     */
    public function __construct(
        private readonly ?string $partialPath = null,
    ) {
    }

    /**
     * Render the given facts to HTML using the cards presenter + partial.
     */
    public function render(PageMomentumFacts $facts): string
    {
        $cards = (new PageMomentumCardsPresenter($facts))->cards();

        return $this->renderCards($cards);
    }

    /**
     * Render an already-prepared list of cards to HTML.
     *
     * @param list<array{key: string, label: string, value: string, hint: string}> $cards
     */
    public function renderCards(array $cards): string
    {
        $partial = $this->partialPath
            ?? dirname(__DIR__, 2) . '/resources/views/admin/page-momentum/cards.php';

        if (!is_file($partial)) {
            throw new \RuntimeException("Page momentum cards partial not found: {$partial}");
        }

        // Expose $cards to the included partial and capture its output.
        $render = static function (string $__partial, array $cards): string {
            ob_start();
            include $__partial;

            return (string) ob_get_clean();
        };

        return $render($partial, $cards);
    }
}
