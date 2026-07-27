<?php

declare(strict_types=1);

namespace Zoosper\Core\Grid;

use Closure;

/**
 * Declarative column definition for the shared admin Grid engine.
 *
 * Every admin listing (audit log, login history, pages, media, sites...) is
 * built from a small, declarative GridDefinition instead of a bespoke
 * template + bespoke pagination class per screen. This is the PHP-declarative
 * approach (Filament-style), not XML (Magento UI Components) — one readable
 * class per grid, IDE-autocompletable, no indirection through multiple files.
 *
 * Note: `callable` cannot be used as a PHP property type (only as a parameter
 * type). The optional render callback is therefore accepted as `?callable` in
 * the constructor and stored internally as a typed `?Closure`.
 */
final readonly class GridColumn
{
    private ?Closure $renderCallback;

    /**
     * @param callable(mixed $value, array<string, mixed> $row): string|null $render
     *        Optional custom cell renderer. Receives the raw column value and
     *        the full row, must return safe (already-escaped) HTML. When null,
     *        the value is cast to string and HTML-escaped automatically.
     */
    public function __construct(
        public string $key,
        public string $label,
        public bool $sortable = false,
        public string $align = 'left',
        ?callable $render = null,
    ) {
        $this->renderCallback = $render !== null ? Closure::fromCallable($render) : null;
    }

    /**
     * @param array<string, mixed> $row
     */
    public function renderValue(mixed $value, array $row): string
    {
        if ($this->renderCallback !== null) {
            return ($this->renderCallback)($value, $row);
        }

        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
