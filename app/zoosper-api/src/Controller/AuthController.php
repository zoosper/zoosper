<?php
declare(strict_types=1);
namespace Zoosper\Api\Controller;
use Zoosper\Auth\Service\AuthService; use Zoosper\Auth\Service\SessionGuard; use Zoosper\Core\Http\JsonResponder; use Zoosper\Core\Http\Request; use Zoosper\Core\Http\Response;
final readonly class AuthController { public function __construct(private JsonResponder $json, private AuthService $auth, private SessionGuard $guard){} public function login(Request $r):Response{$p=$r->json();$u=$this->auth->authenticate((string)($p['email']??''),(string)($p['password']??'')); if($u===null) return $this->json->error('invalid_credentials','Invalid email or password.',401); $this->guard->login($u); return $this->json->success(['user'=>['id'=>$u->id,'email'=>$u->email,'name'=>$u->name,'permissions'=>$u->permissions]]);} public function logout(Request $r):Response{$this->guard->logout(); return $this->json->success(['message'=>'Logged out.']);} }
