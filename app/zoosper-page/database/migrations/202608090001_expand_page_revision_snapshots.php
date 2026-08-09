<?php

declare(strict_types=1);
return static function (PDO $pdo): void {
    $driver=(string)$pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $columns=$driver==='sqlite' ? array_column($pdo->query('PRAGMA table_info(page_revisions)')->fetchAll(PDO::FETCH_ASSOC),'name') : [];
    $definitions=['slug'=>'TEXT NOT NULL DEFAULT \'\'','status'=>"TEXT NOT NULL DEFAULT 'draft'",'content_format'=>"TEXT NOT NULL DEFAULT 'html'",'content_json'=>'TEXT NULL','meta_title'=>'TEXT NULL','meta_description'=>'TEXT NULL','meta_keywords'=>'TEXT NULL','canonical_url'=>'TEXT NULL'];
    if($driver==='sqlite'){foreach($definitions as $name=>$definition){if(!in_array($name,$columns,true)){$pdo->exec("ALTER TABLE page_revisions ADD COLUMN {$name} {$definition}");}} return;}
    foreach(['slug VARCHAR(190) NOT NULL DEFAULT \'\'','status VARCHAR(32) NOT NULL DEFAULT \'draft\'','content_format VARCHAR(32) NOT NULL DEFAULT \'html\'','content_json LONGTEXT NULL','meta_title VARCHAR(255) NULL','meta_description TEXT NULL','meta_keywords TEXT NULL','canonical_url VARCHAR(2048) NULL'] as $definition){$name=strtok($definition,' ');$statement=$pdo->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=\'page_revisions\' AND COLUMN_NAME=:column');$statement->execute(['column'=>$name]);if((int)$statement->fetchColumn()===0){$pdo->exec("ALTER TABLE page_revisions ADD COLUMN {$definition}");}}
};
