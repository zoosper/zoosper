<?php
declare(strict_types=1);
use Zoosper\Database\MigrationInterface;
use Zoosper\Database\Schema\SchemaInspector;
return new class implements MigrationInterface {
 public function name():string{return '202610020003_email_grid_query_indexes';}
 public function up(PDO $pdo,string $driver):void{$i=new SchemaInspector($pdo,$driver);if($i->tableExists('smtp_email_log')&&!$i->indexExists('smtp_email_log','idx_smtp_email_log_status_created_id'))$pdo->exec('CREATE INDEX idx_smtp_email_log_status_created_id ON smtp_email_log (status, created_at, id)');}
};
