<?php
declare(strict_types=1);
function gridPlan(PDO $pdo,string $sql):string{return implode("\n",array_map(static fn(array $r):string=>(string)$r['detail'],$pdo->query('EXPLAIN QUERY PLAN '.$sql)->fetchAll(PDO::FETCH_ASSOC)));}
it('declares module-owned composite indexes for indexable Admin Grid paths',function():void{
 $root=dirname(__DIR__,5);
 $admin=require $root.'/app/zoosper-admin/config/db_schema.php';
 $menu=require $root.'/app/zoosper-menu/config/db_schema.php';
 $mail=require $root.'/app/zoosper-mail/config/db_schema.php';
 $media=require $root.'/packages/zoosper-media/config/db_schema.php';
 expect($admin['tables']['admin_login_history']['indexes']['idx_admin_login_history_status_id']['columns'])->toBe(['status','id'])
  ->and($admin['tables']['admin_activity_log']['indexes']['idx_admin_activity_entity_type_id']['columns'])->toBe(['entity_type','id'])
  ->and($menu['tables']['menus']['indexes']['idx_menus_status_label_id']['columns'])->toBe(['status','label','id'])
  ->and($mail['tables']['smtp_email_log']['indexes']['idx_smtp_email_log_status_created_id']['columns'])->toBe(['status','created_at','id'])
  ->and($media['tables']['media_assets']['indexes'])->toHaveKeys(['idx_media_assets_status_created_id','idx_media_assets_mime_created_id','idx_media_assets_extension_created_id']);
});
it('uses composite indexes for exact-filter Grid ordering without claiming wildcard search coverage',function():void{
 $pdo=new PDO('sqlite::memory:');$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
 $cases=[
  ['CREATE TABLE admin_login_history(id INTEGER PRIMARY KEY,status TEXT)','CREATE INDEX idx_admin_login_history_status_id ON admin_login_history(status,id)',"SELECT id FROM admin_login_history WHERE status='failed' ORDER BY id DESC",'idx_admin_login_history_status_id'],
  ['CREATE TABLE admin_activity_log(id INTEGER PRIMARY KEY,entity_type TEXT)','CREATE INDEX idx_admin_activity_entity_type_id ON admin_activity_log(entity_type,id)',"SELECT id FROM admin_activity_log WHERE entity_type='page' ORDER BY id DESC",'idx_admin_activity_entity_type_id'],
  ['CREATE TABLE menus(id INTEGER PRIMARY KEY,status TEXT,label TEXT)','CREATE INDEX idx_menus_status_label_id ON menus(status,label,id)',"SELECT id FROM menus WHERE status='active' ORDER BY label ASC,id ASC",'idx_menus_status_label_id'],
  ['CREATE TABLE smtp_email_log(id INTEGER PRIMARY KEY,status TEXT,created_at TEXT)','CREATE INDEX idx_smtp_email_log_status_created_id ON smtp_email_log(status,created_at,id)',"SELECT id FROM smtp_email_log WHERE status='sent' ORDER BY created_at DESC,id DESC",'idx_smtp_email_log_status_created_id'],
  ['CREATE TABLE media_assets(id INTEGER PRIMARY KEY,status TEXT,mime_type TEXT,extension TEXT,created_at TEXT)','CREATE INDEX idx_media_assets_status_created_id ON media_assets(status,created_at,id)',"SELECT id FROM media_assets WHERE status='active' ORDER BY created_at DESC,id DESC",'idx_media_assets_status_created_id'],
 ];
 foreach($cases as [$table,$index,$query,$expected]){$pdo->exec($table);$pdo->exec($index);expect(gridPlan($pdo,$query))->toContain($expected);}
});
