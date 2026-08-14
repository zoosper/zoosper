<?php

declare(strict_types=1);
namespace Zoosper\Media\Service;
use Zoosper\Media\Model\MediaAsset;use Zoosper\Media\Repository\MediaDerivativeRepository;
final readonly class MediaDerivativeLookup
{
    public function __construct(private MediaDerivativeRepository $derivatives) {}
    public function publicPath(MediaAsset $asset,string $profile='original'):?string { return $profile==='original'?$asset->publicPath:$this->derivatives->findProfile($asset->id,$profile)?->publicPath; }
}
