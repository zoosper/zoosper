<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Allow-listed page sizes used by the Grid workspace limit selector. */
final readonly class GridWorkspacePageSizeOptions
{
    /** @param list<int> $values */
    public function __construct(public array $values = [20, 50, 100, 200])
    {
        if ($values === [] || count($values) !== count(array_unique($values))) {
            throw new \InvalidArgumentException('Grid page-size options must be unique and non-empty.');
        }
        foreach ($values as $value) {
            if ($value < 1 || $value > 500) {
                throw new \InvalidArgumentException('Grid page size must be between 1 and 500.');
            }
        }
        $sorted = $values;
        sort($sorted);
        if ($sorted !== $values) {
            throw new \InvalidArgumentException('Grid page-size options must be ascending.');
        }
    }

    public function contains(int $value): bool
    {
        return in_array($value, $this->values, true);
    }

    public function normalise(int $value): int
    {
        return $this->contains($value) ? $value : $this->values[0];
    }
}











