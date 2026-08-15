<?php

declare(strict_types=1);
namespace Zoosper\Auth\Token;
use PDO;
final readonly class PersonalAccessTokenRepository
{
    public function __construct(private PDO $pdo){}
    /** @param list<string> $scopes */
    public function create(int $userId,string $publicId,string $name,string $hash,array $scopes,?string $expiresAt,string $now):int
    {
        $s=$this->pdo->prepare('INSERT INTO personal_access_tokens (public_id,admin_user_id,name,token_hash,scopes_json,expires_at,last_used_at,revoked_at,created_at,updated_at) VALUES (:public_id,:admin_user_id,:name,:token_hash,:scopes_json,:expires_at,NULL,NULL,:created_at,:updated_at)');
        $s->execute(['public_id'=>$publicId,'admin_user_id'=>$userId,'name'=>$name,'token_hash'=>$hash,'scopes_json'=>json_encode(array_values($scopes),JSON_THROW_ON_ERROR),'expires_at'=>$expiresAt,'created_at'=>$now,'updated_at'=>$now]);return(int)$this->pdo->lastInsertId();
    }
    public function findByPublicId(string $publicId):?PersonalAccessToken{$s=$this->pdo->prepare('SELECT * FROM personal_access_tokens WHERE public_id=:public_id LIMIT 1');$s->execute(['public_id'=>$publicId]);$r=$s->fetch(PDO::FETCH_ASSOC);return is_array($r)?$this->hydrate($r):null;}
    public function touch(int $id,string $now):void{$s=$this->pdo->prepare('UPDATE personal_access_tokens SET last_used_at=:last_used_at,updated_at=:updated_at WHERE id=:id');$s->execute(['last_used_at'=>$now,'updated_at'=>$now,'id'=>$id]);}
    public function revoke(int $id,int $ownerId,string $now):bool{$s=$this->pdo->prepare('UPDATE personal_access_tokens SET revoked_at=:revoked_at,updated_at=:updated_at WHERE id=:id AND admin_user_id=:owner AND revoked_at IS NULL');$s->execute(['revoked_at'=>$now,'updated_at'=>$now,'id'=>$id,'owner'=>$ownerId]);return $s->rowCount()===1;}
    /** @return list<PersonalAccessToken> */ public function allForUser(int $userId):array{$s=$this->pdo->prepare('SELECT * FROM personal_access_tokens WHERE admin_user_id=:id ORDER BY id DESC');$s->execute(['id'=>$userId]);return array_map(fn(array $r)=>$this->hydrate($r),$s->fetchAll(PDO::FETCH_ASSOC));}
    /** @param array<string,mixed> $r */ private function hydrate(array $r):PersonalAccessToken{$scopes=json_decode((string)$r['scopes_json'],true,512,JSON_THROW_ON_ERROR);return new PersonalAccessToken((int)$r['id'],(string)$r['public_id'],(int)$r['admin_user_id'],(string)$r['name'],(string)$r['token_hash'],array_values(array_filter($scopes,'is_string')),$r['expires_at']!==null?(string)$r['expires_at']:null,$r['last_used_at']!==null?(string)$r['last_used_at']:null,$r['revoked_at']!==null?(string)$r['revoked_at']:null,(string)$r['created_at']);}
}
