<?php

declare(strict_types=1);

namespace Zoosper\Core\Asset;

/**
 * Thrown when an asset cannot be resolved because it is unknown, unsupported,
 * or the requested path is unsafe. Callers should translate this to a 404.
 */
final class AssetNotFoundException extends \RuntimeException
{
}
