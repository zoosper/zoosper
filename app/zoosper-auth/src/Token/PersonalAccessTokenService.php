<?php

declare(strict_types=1);
namespace Zoosper\Auth\Token;
use InvalidArgumentException;
final readonly class PersonalAccessTokenService
{
    public const SCOPES=['pages:read','pages:write','pages:publish','media:read','media:upload','media:delete','menus:read','menus:write','url_rewrites:read','url_rewrites:write','sites:read','sites:write','themes:read','themes:write'];
    public function __construct(private PersonalAccessTokenRepository $tokens){}
    /** @param list<string> $scopes @return array{id:int,token:string,public_id:string} */
    public function issue(int $userId,string $name,array $scopes,?string $expiresAt=null):array
    {
        $name=trim($name);if($name===''||mb_strlen($name)>120)throw new InvalidArgumentException('Token name must be between 1 and 120 characters.');
        $scopes=array_values(array_unique($scopes));if($scopes===[]||array_diff($scopes,self::SCOPES)!==[])throw new InvalidArgumentException('At least one valid token scope is required.');
        if($expiresAt!==null&&strtotime($expiresAt)<=time())throw new InvalidArgumentException('Token expiry must be in the future.');
        $publicId=bin2hex(random_bytes(8));$secret=bin2hex(random_bytes(32));$plain='zp_pat_'.$publicId.'_'.$secret;$now=gmdate('Y-m-d H:i:s');
        $id=$this->tokens->create($userId,$publicId,$name,hash('sha256',$plain),$scopes,$expiresAt,$now);return ['id'=>$id,'token'=>$plain,'public_id'=>$publicId];
    }
}
