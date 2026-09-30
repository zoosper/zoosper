<?php
declare(strict_types=1);
use Zoosper\Database\MigrationInterface;
return new class implements MigrationInterface {
    public function name(): string { return '202609300001_seed_github_issue_permission'; }
    public function up(PDO $pdo, string $driver): void
    {
        if (!in_array($driver, ['mysql', 'sqlite'], true)) throw new RuntimeException('Unsupported database driver.');
        $sql = $driver === 'mysql'
            ? 'INSERT IGNORE INTO admin_permissions (code,label,parent_code,sort_order,created_at) VALUES (:code,:label,:parent_code,:sort_order,:created_at)'
            : 'INSERT OR IGNORE INTO admin_permissions (code,label,parent_code,sort_order,created_at) VALUES (:code,:label,:parent_code,:sort_order,:created_at)';
        $pdo->prepare($sql)->execute(['code'=>'github_issue.view','label'=>'View GitHub issues','parent_code'=>'content','sort_order'=>50,'created_at'=>gmdate('Y-m-d H:i:s')]);
    }
};
