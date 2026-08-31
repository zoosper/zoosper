<?php

declare(strict_types=1);

namespace Zoosper\Menu\Lifecycle;

use PDO;
use RuntimeException;
use Throwable;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Menu\Model\Menu;

/** Menu-owned inactive, restore, and guarded permanent-delete transaction boundary. */
final readonly class MenuLifecycleCoordinator
{
    public function __construct(private PDO $pdo, private MenuReferenceInspector $references, private ?AuditLoggerInterface $audit = null) {}

    public function disable(Menu $menu, int $actorId, string $actorEmail): MenuLifecycleResult
    {
        if ($menu->status === 'inactive') { return new MenuLifecycleResult(true, 'disable', $menu->id, 'inactive', 'inactive', message: 'Menu is already inactive.'); }
        $this->status($menu->id, 'inactive'); $this->log($actorId, $actorEmail, 'menu.disabled', $menu, 'inactive');
        return new MenuLifecycleResult(true, 'disable', $menu->id, $menu->status, 'inactive');
    }

    public function restore(Menu $menu, int $actorId, string $actorEmail): MenuLifecycleResult
    {
        if ($menu->status !== 'inactive') { return new MenuLifecycleResult(false, 'restore', $menu->id, $menu->status, blockers: ['status' => 1], message: 'Only inactive Menus can be restored.'); }
        $this->status($menu->id, 'active'); $this->log($actorId, $actorEmail, 'menu.restored', $menu, 'active');
        return new MenuLifecycleResult(true, 'restore', $menu->id, 'inactive', 'active');
    }

    public function deletePermanently(Menu $menu, int $actorId, string $actorEmail): MenuLifecycleResult
    {
        if ($menu->status !== 'inactive') { return new MenuLifecycleResult(false, 'delete', $menu->id, $menu->status, blockers: ['status' => 1], message: 'Make the Menu inactive before permanent deletion.'); }
        $blockers = array_filter($this->references->counts($menu->id), static fn (int $count): bool => $count > 0);
        if ($blockers !== []) { return new MenuLifecycleResult(false, 'delete', $menu->id, 'inactive', blockers: $blockers, message: 'Remove Menu items before permanent deletion.'); }
        $started = !$this->pdo->inTransaction(); if ($started) { $this->pdo->beginTransaction(); }
        try { $statement=$this->pdo->prepare('DELETE FROM menus WHERE id=:id');$statement->execute(['id'=>$menu->id]);if($started){$this->pdo->commit();} }
        catch(Throwable $exception){if($started&&$this->pdo->inTransaction()){$this->pdo->rollBack();}throw new RuntimeException('Permanent Menu deletion failed and was rolled back.',previous:$exception);}
        $this->log($actorId,$actorEmail,'menu.deleted_permanently',$menu,null);
        return new MenuLifecycleResult(true,'delete',$menu->id,'inactive',null);
    }

    private function status(int $id,string $status): void{$statement=$this->pdo->prepare('UPDATE menus SET status=:status,updated_at=:updated_at WHERE id=:id');$statement->execute(['id'=>$id,'status'=>$status,'updated_at'=>gmdate('Y-m-d H:i:s')]);}
    private function log(int $actorId,string $email,string $action,Menu $menu,?string $newStatus): void{$this->audit?->logAction($actorId,$email,$action,'menu',(string)$menu->id,$action,['menu_id'=>$menu->id,'site_id'=>$menu->siteId,'previous_status'=>$menu->status,'new_status'=>$newStatus]);}
}










