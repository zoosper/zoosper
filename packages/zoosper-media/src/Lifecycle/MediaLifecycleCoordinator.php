<?php
declare(strict_types=1);
namespace Zoosper\Media\Lifecycle;
use PDO; use RuntimeException; use Throwable; use Zoosper\Core\Audit\AuditLoggerInterface; use Zoosper\Media\Model\MediaAsset; use Zoosper\Media\Repository\MediaAssetRepository; use Zoosper\Media\Service\MediaStoredFileCleanupService;
final readonly class MediaLifecycleCoordinator {
 public function __construct(private PDO $pdo,private MediaAssetRepository $assets,private MediaStoredFileCleanupService $cleanup,private ?AuditLoggerInterface $audit=null){}
 public function archive(MediaAsset $a,int $id,string $email):bool { if($a->status==='archived')return true;$this->assets->changeStatus($a->id,'archived');$this->log($id,$email,'media.archived',$a,'archived');return true; }
 public function restore(MediaAsset $a,int $id,string $email):bool { if($a->status!=='archived')return false;$this->assets->changeStatus($a->id,'active');$this->log($id,$email,'media.restored',$a,'active');return true; }
 public function deletePermanently(MediaAsset $a,int $id,string $email):bool { if($a->status!=='archived')return false;$own=!$this->pdo->inTransaction();if($own)$this->pdo->beginTransaction();try{$this->assets->deletePermanently($a->id);if($own)$this->pdo->commit();}catch(Throwable $e){if($own&&$this->pdo->inTransaction())$this->pdo->rollBack();throw new RuntimeException('Permanent Media deletion failed and was rolled back.',previous:$e);} $r=$this->cleanup->cleanup($a);$this->log($id,$email,'media.deleted_permanently',$a,null,['cleanup_deleted'=>count($r->deleted),'cleanup_skipped'=>count($r->skipped)]);return true; }
 private function log(int $id,string $email,string $action,MediaAsset $a,?string $status,array $extra=[]):void{$this->audit?->logAction($id,$email,$action,'media_asset',(string)$a->id,$action,$extra+['asset_id'=>$a->id,'previous_status'=>$a->status,'new_status'=>$status]);}
}
