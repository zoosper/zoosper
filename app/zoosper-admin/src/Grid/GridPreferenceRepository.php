<?php
declare(strict_types=1);
namespace Zoosper\Admin\Grid;
use PDO;
final readonly class GridPreferenceRepository
{
    public function __construct(private PDO $pdo) {}
    /** @return list<string>|null */
    public function findVisibleColumns(int $adminUserId, string $gridKey): ?array
    {
        $st=$this->pdo->prepare('SELECT visible_columns_json FROM admin_grid_preferences WHERE admin_user_id = :uid AND grid_key = :grid LIMIT 1');
        $st->execute(['uid'=>$adminUserId,'grid'=>$gridKey]);
        $json=$st->fetchColumn();
        if(!is_string($json)||$json===''){return null;}
        $decoded=json_decode($json,true);
        return is_array($decoded)?array_values(array_map(static fn(mixed $v): string => (string)$v,$decoded)):null;
    }
    /** @param list<string> $visibleColumnKeys */
    public function saveVisibleColumns(int $adminUserId, string $gridKey, array $visibleColumnKeys): void
    {
        $json=json_encode(array_values($visibleColumnKeys), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $now=gmdate('Y-m-d H:i:s');
        $existing=$this->pdo->prepare('SELECT id FROM admin_grid_preferences WHERE admin_user_id = :uid AND grid_key = :grid LIMIT 1');
        $existing->execute(['uid'=>$adminUserId,'grid'=>$gridKey]);
        $id=$existing->fetchColumn();
        if($id!==false){$up=$this->pdo->prepare('UPDATE admin_grid_preferences SET visible_columns_json = :json, updated_at = :updated_at WHERE id = :id'); $up->execute(['json'=>$json,'updated_at'=>$now,'id'=>(int)$id]); return;}
        $in=$this->pdo->prepare('INSERT INTO admin_grid_preferences (admin_user_id, grid_key, visible_columns_json, updated_at) VALUES (:uid, :grid, :json, :updated_at)');
        $in->execute(['uid'=>$adminUserId,'grid'=>$gridKey,'json'=>$json,'updated_at'=>$now]);
    }
    public function clear(int $adminUserId, string $gridKey): void
    {
        $st=$this->pdo->prepare('DELETE FROM admin_grid_preferences WHERE admin_user_id = :uid AND grid_key = :grid');
        $st->execute(['uid'=>$adminUserId,'grid'=>$gridKey]);
    }
}
