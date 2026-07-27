<?php
declare(strict_types=1);
namespace Zoosper\Core\Grid;
use Closure;
final readonly class GridColumn
{
    private ?Closure $renderCallback;
    public function __construct(
        public string $key,
        public string $label,
        public bool $sortable = false,
        public string $align = 'left',
        ?callable $render = null,
        public bool $toggleable = true,
        public bool $defaultVisible = true,
    ) {
        $this->renderCallback = $render !== null ? Closure::fromCallable($render) : null;
    }
    /** @param array<string,mixed> $row */
    public function renderValue(mixed $value, array $row): string
    {
        if ($this->renderCallback !== null) {
            return ($this->renderCallback)($value, $row);
        }
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
