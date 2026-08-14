<?php

declare(strict_types=1);
namespace Zoosper\Media\Service;
interface MediaCanonicalizerInterface
{
    public function canonicalize(string $sourcePath, string $destinationPath, string $extension): void;
}
