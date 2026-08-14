<?php

declare(strict_types=1);
namespace Zoosper\Media\Model;
final readonly class MediaDerivative
{
    public function __construct(public int $id,public int $mediaAssetId,public string $profile,public string $format,public int $width,public int $height,public int $sizeBytes,public string $storagePath,public string $publicPath,public ?string $createdAt=null,public ?string $updatedAt=null) {}
}
