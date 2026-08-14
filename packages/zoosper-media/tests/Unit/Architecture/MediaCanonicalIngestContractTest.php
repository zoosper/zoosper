<?php

declare(strict_types=1);
it('publishes only canonical media rather than raw upload bytes',function():void{
 $root=dirname(__DIR__,3);$storage=(string)file_get_contents($root.'/src/Service/MediaStorage.php');$services=(string)file_get_contents($root.'/config/services.php');
 expect($storage)->toContain('?MediaCanonicalizerInterface $canonicalizer = null')->toContain('new GdMediaCanonicalizer())->canonicalize($tmpName, $storagePath, $extension)')->not->toContain('copy($tmpName, $storagePath)')->and($services)->toContain('GdMediaCanonicalizer');
});
