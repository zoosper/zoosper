<?php

declare(strict_types=1);
namespace Zoosper\Auth\Token;
use Zoosper\Auth\Repository\AdminUserRepository;
final readonly class PersonalAccessTokenAuthenticator
{
    public function __construct(private PersonalAccessTokenRepository $tokens,private AdminUserRepository $users){}
    public function authenticate(?string $authorization):?PersonalAccessTokenPrincipal
    {
        if($authorization===null||preg_match('/^Bearer (zp_pat_([a-f0-9]{16})_[a-f0-9]{64})$/D',$authorization,$m)!==1)return null;
        $token=$this->tokens->findByPublicId($m[2]);if($token===null||$token->isRevoked()||$token->isExpired()||!hash_equals($token->tokenHash,hash('sha256',$m[1])))return null;
        $user=$this->users->findById($token->adminUserId);if($user===null||$user->status!=='active')return null;
        $now=time();$this->tokens->touch($token->id,gmdate('Y-m-d H:i:s',$now),gmdate('Y-m-d H:i:s',$now-300));return new PersonalAccessTokenPrincipal($token,$user);
    }
}










