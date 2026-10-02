<?php
declare(strict_types=1);
use Zoosper\Database\MigrationInterface;
use Zoosper\Database\Schema\SchemaInspector;
return new class implements MigrationInterface {
 public function name():string{return '202610020004_media_grid_query_indexes';}
 public function up(PDO $pdo,string $driver):void{
  $i=new SchemaInspector($pdo,$driver);
  foreach([
   ['idx_media_assets_status_created_id','status, created_at, id'],
   ['idx_media_assets_mime_created_id','mime_type, created_at, id'],
   ['idx_media_assets_extension_created_id','extension, created_at, id'],
  ] as [$index,$columns])if($i->tableExists('media_assets')&&!$i->indexExists('media_assets',$index))$pdo->exec("CREATE INDEX $index ON media_assets ($columns)");
 }
};
