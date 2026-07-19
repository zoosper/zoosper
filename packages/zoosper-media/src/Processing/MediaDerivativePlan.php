<?php

declare(strict_types=1);

namespace Zoosper\Media\Processing;

/**
 * Collection of derivative profiles to apply for one media asset.
 */
final readonly class MediaDerivativePlan
{
    /** @var list<MediaDerivativeProfile> */
    public array $profiles;

    public function __construct(MediaDerivativeProfile ...$profiles)
    {
        $this->profiles = array_values($profiles);
    }

    public function isEmpty(): bool
    {
        return $this->profiles === [];
    }

    /** @return list<string> */
    public function codes(): array
    {
        return array_map(static fn (MediaDerivativeProfile $profile): string => $profile->code, $this->profiles);
    }
}
