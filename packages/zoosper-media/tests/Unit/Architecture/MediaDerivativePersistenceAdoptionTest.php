<?php

declare(strict_types=1);
it('persists derivatives from a reloaded MediaAsset and exposes lookup',function():void{$root=dirname(__DIR__,3);$upload=(string)file_get_contents($root.'/src/Service/MediaUploadService.php');$dispatch=(string)file_get_contents($root.'/src/Processing/MediaUploadDerivativeDispatcher.php');$services=(string)file_get_contents($root.'/config/services.php');expect($upload)->toContain('$this->assets->findById((int) $assetId)')->toContain('processAfterUpload($asset)')->toContain('$this->assets->deletePermanently((int) $assetId)')->and($dispatch)->toContain('replaceForAsset($assetOrStoragePath, $plan)')->and($services)->toContain('MediaDerivativeLookup::class');});











