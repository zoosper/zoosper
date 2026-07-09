<?php
declare(strict_types=1);
namespace Zoosper\Core\Database;
use PDO; use RuntimeException;
final readonly class Migrator {
    public function __construct(private PDO $pdo, private string $path) {}
    public function migrate(): void { $driver=(string)$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME); $this->table($driver); $ran=$this->ran(); foreach($this->files() as $file){ $m=require $file; if(!$m instanceof MigrationInterface) throw new RuntimeException('Bad migration '.$file); if(in_array($m->name(),$ran,true)) continue; $m->up($this->pdo,$driver); $s=$this->pdo->prepare('INSERT INTO migrations (migration,migrated_at) VALUES (:m,:t)'); $s->execute(['m'=>$m->name(),'t'=>gmdate('Y-m-d H:i:s')]); } }
    private function table(string $d): void { $this->pdo->exec($d==='mysql' ? 'CREATE TABLE IF NOT EXISTS migrations (id INT AUTO_INCREMENT PRIMARY KEY, migration VARCHAR(255) NOT NULL UNIQUE, migrated_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : 'CREATE TABLE IF NOT EXISTS migrations (id INTEGER PRIMARY KEY AUTOINCREMENT, migration TEXT NOT NULL UNIQUE, migrated_at TEXT NOT NULL)'); }
    private function ran(): array { return array_map(fn($r)=>(string)$r['migration'],$this->pdo->query('SELECT migration FROM migrations')->fetchAll()); }
    private function files(): array { $f=glob($this->path.'/*.php') ?: []; sort($f); return $f; }
}
