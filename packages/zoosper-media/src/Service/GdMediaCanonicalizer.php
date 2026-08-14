<?php

declare(strict_types=1);
namespace Zoosper\Media\Service;
use GdImage;use RuntimeException;
/** Decodes and freshly encodes supported raster uploads before publication. */
final readonly class GdMediaCanonicalizer implements MediaCanonicalizerInterface
{
    private const MAX_PIXELS = 40_000_000;
    public function canonicalize(string $sourcePath,string $destinationPath,string $extension):void
    {
        if(!extension_loaded('gd'))throw new RuntimeException('GD is required for secure media canonicalisation.');
        $dimensions=@getimagesize($sourcePath);
        if(!is_array($dimensions)||($dimensions[0]*$dimensions[1])>self::MAX_PIXELS)throw new RuntimeException('Media dimensions are invalid or exceed the safe pixel limit.');
        $extension=strtolower($extension);
        $image=match($extension){'jpg','jpeg'=>@imagecreatefromjpeg($sourcePath),'png'=>@imagecreatefrompng($sourcePath),'webp'=>@imagecreatefromwebp($sourcePath),'gif'=>@imagecreatefromgif($sourcePath),default=>false};
        if(!$image instanceof GdImage)throw new RuntimeException('Unable to decode uploaded media into a trusted raster image.');
        try{
            if(in_array($extension,['png','webp','gif'],true)){imagealphablending($image,false);imagesavealpha($image,true);}
            $written=match($extension){'jpg','jpeg'=>imagejpeg($image,$destinationPath,90),'png'=>imagepng($image,$destinationPath,6),'webp'=>imagewebp($image,$destinationPath,85),'gif'=>imagegif($image,$destinationPath),default=>false};
            if(!$written)throw new RuntimeException('Unable to write canonical media output.');
        } finally {
            // PHP 8.5 releases GdImage objects automatically; explicit destruction is unnecessary.
            unset($image);
        }
    }
}
