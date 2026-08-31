<?php

declare(strict_types=1);
it('declares derivative cascade ownership and lifecycle file cleanup',function():void{$root=dirname(__DIR__,3);$schema=(string)file_get_contents($root.'/config/db_schema.php');$life=(string)file_get_contents($root.'/src/Lifecycle/MediaLifecycleCoordinator.php');expect($schema)->toContain("'media_derivatives' => [")->toContain("'on_delete' => 'CASCADE'")->and($life)->toContain('$this->derivatives->forAsset($asset->id)')->toContain("'storagePath' => \$derivative->storagePath")->toContain("'publicPath' => \$derivative->publicPath");});











