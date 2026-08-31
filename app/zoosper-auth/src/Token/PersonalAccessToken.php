<?php

declare(strict_types=1);
namespace Zoosper\Auth\Token;
final readonly class PersonalAccessToken
{
    /** @param list<string> $scopes */
    public function __construct(public int $id,public string $publicId,public int $adminUserId,public string $name,public string $tokenHash,public array $scopes,public ?string $expiresAt,public ?string $lastUsedAt,public ?string $revokedAt,public string $createdAt){}
    public function isRevoked():bool{return $this->revokedAt!==null;}
    public function isExpired(?int $now=null):bool{return $this->expiresAt!==null&&strtotime($this->expiresAt)<=($now??time());}
    public function allows(string $scope):bool{return in_array($scope,$this->scopes,true);}
}










