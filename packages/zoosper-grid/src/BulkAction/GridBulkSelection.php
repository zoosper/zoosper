<?php

declare(strict_types=1);

namespace Zoosper\Grid\BulkAction;

use InvalidArgumentException;

/** Validated explicit row identities submitted to a bulk-action executor. */
final readonly class GridBulkSelection
{
    /** @var list<string> */
    public array $identities;

    /** @param list<int|string> $identities */
    public function __construct(array $identities, int $maximumSelection)
    {
        $normalised = [];
        foreach ($identities as $identity) {
            $value = trim((string) $identity);
            if ($value === '') {
                throw new InvalidArgumentException('Grid selection cannot contain an empty identity.');
            }
            if (!in_array($value, $normalised, true)) {
                $normalised[] = $value;
            }
        }
        if ($normalised === []) {
            throw new InvalidArgumentException('Grid bulk actions require at least one selected identity.');
        }
        if (count($normalised) > $maximumSelection) {
            throw new InvalidArgumentException('Grid selection exceeds the action maximum.');
        }
        $this->identities = $normalised;
    }

    public function count(): int
    {
        return count($this->identities);
    }
}
