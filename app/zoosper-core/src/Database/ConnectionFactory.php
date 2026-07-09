<?php
declare(strict_types=1);
namespace Zoosper\Core\Database;
use PDO; use RuntimeException; use Zoosper\Core\Config\ConfigRepository;
final readonly class ConnectionFactory {
    public function __construct(private ConfigRepository $config, private string $basePath) {}
    public function create(): PDO { $default=(string)$this->config->get('database.default','sqlite'); $c=$this->config->get('database.connections.'.$default); if(!is_array($c)) throw new RuntimeException('Database connection not configured'); return ($c['driver']??$default)==='mysql' ? $this->mysql($c) : $this->sqlite($c); }
    private function sqlite(array $c): PDO { $db=(string)($c['database']??'storage/database/zoosper.sqlite'); $path=str_starts_with($db,'/')?$db:$this->basePath.'/'.$db; if(!is_dir(dirname($path))) mkdir(dirname($path),0775,true); $pdo=new PDO('sqlite:'.$path); $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC); $pdo->exec('PRAGMA foreign_keys = ON'); return $pdo; }
    private function mysql(array $c): PDO { $dsn=sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',(string)$c['host'],(int)$c['port'],(string)$c['database'],(string)($c['charset']??'utf8mb4')); $pdo=new PDO($dsn,(string)$c['username'],(string)$c['password']); $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION); $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC); return $pdo; }
}
