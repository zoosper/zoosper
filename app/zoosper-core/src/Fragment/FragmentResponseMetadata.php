<?php

declare(strict_types=1);

namespace Zoosper\Core\Fragment;

use Zoosper\Core\Cache\HttpCachePolicy;

/**
 * Response metadata for AJAX fragments.
 *
 * This object is intentionally metadata-only so controllers can apply headers
 * without mixing rendering, routing or inline HTML into the cache layer.
 */
final readonly class FragmentResponseMetadata
{
    public function __construct(public string $fragmentCode, public HttpCachePolicy $policy)
    {
    }

    /**
     * @return array<string, string>
     */
    public function headers(): array
    {
        return $this->policy->headers + [
            'X-Zoosper-Fragment' => $this->fragmentCode,
        ];
    }
}
