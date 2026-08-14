<?php

declare(strict_types=1);
namespace Zoosper\Media\Repository;
use PDO;use RuntimeException;use Throwable;use Zoosper\Media\Model\MediaAsset;use Zoosper\Media\Model\MediaDerivative;use Zoosper\Media\Processing\LocalMediaDerivativePathResolver;use Zoosper\Media\Processing\MediaDerivativePlan;
/** Owns persisted metadata for generated Media derivatives. */
final readonly class MediaDerivativeRepository
{
    public function __construct(private PDO $pdo,private string $basePath,private ?LocalMediaDerivativePathResolver $paths=null) {}
    /** @return list<MediaDerivative> */
    public function forAsset(int $assetId):array { $s=$this->pdo->prepare('SELECT * FROM media_derivatives WHERE media_asset_id = :id ORDER BY profile, format');$s->execute(['id'=>$assetId]);return array_map($this->hydrate(...),$s->fetchAll(PDO::FETCH_ASSOC)?:[]); }
    public function findProfile(int $assetId,string $profile):?MediaDerivative { $s=$this->pdo->prepare('SELECT * FROM media_derivatives WHERE media_asset_id = :id AND profile = :profile LIMIT 1');$s->execute(['id'=>$assetId,'profile'=>$profile]);$r=$s->fetch(PDO::FETCH_ASSOC);return is_array($r)?$this->hydrate($r):null; }
    public function replaceForAsset(MediaAsset $asset,MediaDerivativePlan $plan):void
    {
        $resolver=$this->paths??new LocalMediaDerivativePathResolver($this->basePath);$rows=[];$now=gmdate('Y-m-d H:i:s');
        foreach($plan->profiles as $profile){$path=$resolver->resolve($asset->storagePath,$profile->code,$profile->format);$public=rtrim($this->basePath,'/').'/public'.$path->publicPath;$info=@getimagesize($path->absolutePath);if(!is_file($path->absolutePath)||!is_file($public)||!is_array($info))throw new RuntimeException('Generated derivative files are incomplete for profile: '.$profile->code);$rows[]=['asset'=>$asset->id,'profile'=>$profile->code,'format'=>$profile->format,'width'=>(int)$info[0],'height'=>(int)$info[1],'size'=>(int)filesize($path->absolutePath),'storage'=>$path->relativePath,'public'=>$path->publicPath,'created_at'=>$now,'updated_at'=>$now];}
        $own=!$this->pdo->inTransaction();if($own)$this->pdo->beginTransaction();
        try{$d=$this->pdo->prepare('DELETE FROM media_derivatives WHERE media_asset_id = :id');$d->execute(['id'=>$asset->id]);$i=$this->pdo->prepare('INSERT INTO media_derivatives (media_asset_id, profile, format, width, height, size_bytes, storage_path, public_path, created_at, updated_at) VALUES (:asset, :profile, :format, :width, :height, :size, :storage, :public, :created_at, :updated_at)');foreach($rows as $row)$i->execute($row);if($own)$this->pdo->commit();}catch(Throwable $e){if($own&&$this->pdo->inTransaction())$this->pdo->rollBack();throw $e;}
    }
    /** @param array<string,mixed> $r */ private function hydrate(array $r):MediaDerivative{return new MediaDerivative((int)$r['id'],(int)$r['media_asset_id'],(string)$r['profile'],(string)$r['format'],(int)$r['width'],(int)$r['height'],(int)$r['size_bytes'],(string)$r['storage_path'],(string)$r['public_path'],(string)$r['created_at'],(string)$r['updated_at']);}
}
