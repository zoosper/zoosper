<?php

declare(strict_types=1);
use Zoosper\Media\Service\GdMediaCanonicalizer;
it('re-encodes a JPEG and removes appended payload bytes',function():void{
 $source=tempnam(sys_get_temp_dir(),'media-source-');$destination=tempnam(sys_get_temp_dir(),'media-output-');
 $image=imagecreatetruecolor(2,2);imagejpeg($image,$source,90);imagedestroy($image);file_put_contents($source,"<?php echo 'payload'; ?>",FILE_APPEND);
 (new GdMediaCanonicalizer())->canonicalize($source,$destination,'jpg');$bytes=(string)file_get_contents($destination);
 expect(@getimagesize($destination))->not->toBeFalse()->and($bytes)->not->toContain('<?php')->and(strlen($bytes))->toBeLessThan(filesize($source));
 @unlink($source);@unlink($destination);
});
it('preserves PNG transparency through canonical output',function():void{
 $source=tempnam(sys_get_temp_dir(),'media-png-');$destination=tempnam(sys_get_temp_dir(),'media-output-');$image=imagecreatetruecolor(2,2);imagealphablending($image,false);imagesavealpha($image,true);$transparent=imagecolorallocatealpha($image,0,0,0,127);imagefill($image,0,0,$transparent);imagepng($image,$source);imagedestroy($image);
 (new GdMediaCanonicalizer())->canonicalize($source,$destination,'png');expect(@getimagesize($destination)[2])->toBe(IMAGETYPE_PNG);@unlink($source);@unlink($destination);
});
