<?php
declare(strict_types=1);
use Zoosper\Database\MigrationInterface;
return new class implements MigrationInterface {
 public function name(): string { return '202608100001_create_menu_tables'; }
 public function up(PDO $pdo,string $driver): void {
  if($driver==='mysql'){
   $e=' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
   $pdo->exec("CREATE TABLE IF NOT EXISTS menus (id INT AUTO_INCREMENT PRIMARY KEY,site_id INT NOT NULL,code VARCHAR(120) NOT NULL,label VARCHAR(190) NOT NULL,status VARCHAR(32) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,UNIQUE KEY uniq_menus_site_code(site_id,code),INDEX idx_menus_site_status(site_id,status),FOREIGN KEY(site_id) REFERENCES sites(id) ON DELETE CASCADE)".$e);
   $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (id INT AUTO_INCREMENT PRIMARY KEY,menu_id INT NOT NULL,parent_id INT NULL,page_id INT NULL,label VARCHAR(190) NOT NULL,url VARCHAR(2048) NULL,target VARCHAR(16) NOT NULL DEFAULT '_self',position INT NOT NULL DEFAULT 0,status VARCHAR(32) NOT NULL DEFAULT 'active',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,INDEX idx_menu_items_menu_parent_position(menu_id,parent_id,position),INDEX idx_menu_items_page(page_id),FOREIGN KEY(menu_id) REFERENCES menus(id) ON DELETE CASCADE,FOREIGN KEY(parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,FOREIGN KEY(page_id) REFERENCES pages(id) ON DELETE SET NULL)".$e); return;
  }
  $pdo->exec("CREATE TABLE IF NOT EXISTS menus (id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER NOT NULL,code TEXT NOT NULL,label TEXT NOT NULL,status TEXT NOT NULL DEFAULT 'active',created_at TEXT NOT NULL,updated_at TEXT NOT NULL,UNIQUE(site_id,code),FOREIGN KEY(site_id) REFERENCES sites(id) ON DELETE CASCADE)");
  $pdo->exec('CREATE INDEX IF NOT EXISTS idx_menus_site_status ON menus(site_id,status)');
  $pdo->exec("CREATE TABLE IF NOT EXISTS menu_items (id INTEGER PRIMARY KEY AUTOINCREMENT,menu_id INTEGER NOT NULL,parent_id INTEGER NULL,page_id INTEGER NULL,label TEXT NOT NULL,url TEXT NULL,target TEXT NOT NULL DEFAULT '_self',position INTEGER NOT NULL DEFAULT 0,status TEXT NOT NULL DEFAULT 'active',created_at TEXT NOT NULL,updated_at TEXT NOT NULL,FOREIGN KEY(menu_id) REFERENCES menus(id) ON DELETE CASCADE,FOREIGN KEY(parent_id) REFERENCES menu_items(id) ON DELETE CASCADE,FOREIGN KEY(page_id) REFERENCES pages(id) ON DELETE SET NULL)");
  $pdo->exec('CREATE INDEX IF NOT EXISTS idx_menu_items_menu_parent_position ON menu_items(menu_id,parent_id,position)'); $pdo->exec('CREATE INDEX IF NOT EXISTS idx_menu_items_page ON menu_items(page_id)');
 }
};










