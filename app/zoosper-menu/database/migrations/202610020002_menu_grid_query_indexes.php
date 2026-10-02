<?php
declare(strict_types=1);
use Zoosper\Database\MigrationInterface;
use Zoosper\Database\Schema\SchemaInspector;
return new class implements MigrationInterface {
 public function name():string{return '202610020002_menu_grid_query_indexes';}
 public function up(PDO $pdo,string $driver):void{$i=new SchemaInspector($pdo,$driver);if($i->tableExists('menus')&&!$i->indexExists('menus','idx_menus_status_label_id'))$pdo->exec('CREATE INDEX idx_menus_status_label_id ON menus (status, label, id)');}
};
