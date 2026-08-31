<?php

declare(strict_types=1);
use Zoosper\Media\Model\MediaAsset;use Zoosper\Media\Processing\MediaDerivativePlan;use Zoosper\Media\Processing\MediaDerivativeProfile;use Zoosper\Media\Processing\LocalMediaDerivativePathResolver;use Zoosper\Media\Repository\MediaDerivativeRepository;
it('persists and replaces generated derivative metadata',function():void{$root=sys_get_temp_dir().'/media-derivative-repo-'.bin2hex(random_bytes(4));$pdo=new PDO('sqlite::memory:');$pdo->exec('PRAGMA foreign_keys=ON');$pdo->exec('CREATE TABLE media_assets(id INTEGER PRIMARY KEY)');$pdo->exec('CREATE TABLE media_derivatives(id INTEGER PRIMARY KEY AUTOINCREMENT,media_asset_id INTEGER NOT NULL,profile TEXT NOT NULL,format TEXT NOT NULL,width INTEGER NOT NULL,height INTEGER NOT NULL,size_bytes INTEGER NOT NULL,storage_path TEXT NOT NULL,public_path TEXT NOT NULL,created_at TEXT NOT NULL,updated_at TEXT NOT NULL,UNIQUE(media_asset_id,profile,format),FOREIGN KEY(media_asset_id) REFERENCES media_assets(id) ON DELETE CASCADE)');$pdo->exec('INSERT INTO media_assets(id) VALUES(1)');$asset=new MediaAsset(1,'u','f.png','f.png','image/png','png',1,'storage/media/original/f.png','/media/f.png','active');$profile=new MediaDerivativeProfile('thumb',320,240,'webp');$path=(new LocalMediaDerivativePathResolver($root))->resolve($asset->storagePath,'thumb','webp');@mkdir(dirname($path->absolutePath),0775,true);@mkdir(dirname($root.'/public'.$path->publicPath),0775,true);$im=imagecreatetruecolor(2,2);imagewebp($im,$path->absolutePath);imagewebp($im,$root.'/public'.$path->publicPath);unset($im);$repo=new MediaDerivativeRepository($pdo,$root);$repo->replaceForAsset($asset,new MediaDerivativePlan($profile));$row=$repo->findProfile(1,'thumb');expect($row)->not->toBeNull()->and($row->width)->toBe(2)->and($row->publicPath)->toBe($path->publicPath);$pdo->exec('DELETE FROM media_assets WHERE id=1');expect($repo->forAsset(1))->toBe([]);});











