<?php

declare(strict_types=1);
namespace Zoosper\Core\Http;
use InvalidArgumentException;
/** Explicit, exact-origin CORS policy for API routes. */
final readonly class CorsPolicy
{
    /** @param list<string> $origins @param list<string> $methods @param list<string> $headers @param list<string> $exposedHeaders */
    public function __construct(public bool $enabled,private array $origins,private array $methods=['GET','POST','PUT','PATCH','DELETE','OPTIONS'],private array $headers=['Content-Type','Authorization','X-CSRF-Token'],private array $exposedHeaders=['ETag','Retry-After'],private bool $credentials=false,private int $maxAge=600)
    {
        if($credentials&&in_array('*',$origins,true))throw new InvalidArgumentException('Credentialed CORS cannot use a wildcard origin.');
    }
    public static function fromEnvironment():self
    {
        $value=static function(string $key,mixed $default=null):mixed{$resolved=getenv($key);return $resolved===false||$resolved===''?$default:$resolved;};
        $list=static fn(string $key,array $default=[]):array=>array_values(array_filter(array_map('trim',explode(',',(string)$value($key,implode(',',$default)))),static fn(string $v):bool=>$v!==''));
        return new self(filter_var($value('API_CORS_ENABLED',false),FILTER_VALIDATE_BOOL),$list('API_CORS_ALLOWED_ORIGINS'),array_map('strtoupper',$list('API_CORS_ALLOWED_METHODS',['GET','POST','PUT','PATCH','DELETE','OPTIONS'])),$list('API_CORS_ALLOWED_HEADERS',['Content-Type','Authorization','X-CSRF-Token']),$list('API_CORS_EXPOSED_HEADERS',['ETag','Retry-After']),filter_var($value('API_CORS_ALLOW_CREDENTIALS',false),FILTER_VALIDATE_BOOL),max(0,(int)$value('API_CORS_MAX_AGE',600)));
    }
    public function allowsOrigin(?string $origin):bool{return $this->enabled&&$origin!==null&&in_array($origin,$this->origins,true);}
    /** @return array<string,string> */
    public function headersFor(string $origin,bool $preflight=false):array
    {
        if(!$this->allowsOrigin($origin))return [];
        $h=['Access-Control-Allow-Origin'=>$origin,'Vary'=>'Origin'];
        if($this->credentials)$h['Access-Control-Allow-Credentials']='true';
        if($preflight){$h['Access-Control-Allow-Methods']=implode(', ',$this->methods);$h['Access-Control-Allow-Headers']=implode(', ',$this->headers);$h['Access-Control-Max-Age']=(string)$this->maxAge;}
        elseif($this->exposedHeaders!==[])$h['Access-Control-Expose-Headers']=implode(', ',$this->exposedHeaders);
        return $h;
    }
}
