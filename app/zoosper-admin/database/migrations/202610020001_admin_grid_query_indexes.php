<?php
declare(strict_types=1);
use Zoosper\Database\MigrationInterface;
use Zoosper\Database\Schema\SchemaInspector;
return new class implements MigrationInterface {
 public function name():string{return '202610020001_admin_grid_query_indexes';}
 public function up(PDO $pdo,string $driver):void{
  $i=new SchemaInspector($pdo,$driver);
  foreach([
   ['admin_login_history','idx_admin_login_history_status_id','status, id'],
   ['admin_activity_log','idx_admin_activity_entity_type_id','entity_type, id'],
  ] as [$table,$index,$columns]){
   if($i->tableExists($table)&&!$i->indexExists($table,$index))$pdo->exec("CREATE INDEX $index ON $table ($columns)");
  }
 }
};
