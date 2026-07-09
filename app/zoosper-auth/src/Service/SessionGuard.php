<?php
declare(strict_types=1);
namespace Zoosper\Auth\Service;
use Zoosper\Auth\Model\AdminUser; use Zoosper\Auth\Repository\AdminUserRepository;
final readonly class SessionGuard { public function __construct(private AdminUserRepository $users){} public function login(AdminUser $u):void{session_regenerate_id(true);$_SESSION['admin_user_id']=$u->id;} public function logout():void{$_SESSION=[]; if(session_status()===PHP_SESSION_ACTIVE) session_destroy();} public function user():?AdminUser{$id=$_SESSION['admin_user_id']??null;return is_numeric($id)?$this->users->findById((int)$id):null;} public function requirePermission(string $p):?AdminUser{$u=$this->user();return $u!==null&&$u->can($p)?$u:null;} }
