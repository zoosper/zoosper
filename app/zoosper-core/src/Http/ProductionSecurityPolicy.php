<?php

declare(strict_types=1);
namespace Zoosper\Core\Http;
use RuntimeException;
/** Fail-closed production defaults for session and authentication controls. */
final class ProductionSecurityPolicy
{
  public static function assertEnvironment():void
  {
      $value=static function(string $key,mixed $default=null):mixed{$resolved=getenv($key);return $resolved===false||$resolved===''?$default:$resolved;};
      $environment=strtolower(trim((string)$value('APP_ENV','')));
      $recognised=['local','development','testing','staging','production'];
      if(!in_array($environment,$recognised,true))throw new RuntimeException('APP_ENV must be one of: local, development, testing, staging, production.');
      if(!in_array($environment,['staging','production'],true))return;
      if(!filter_var($value('SESSION_SECURE',false),FILTER_VALIDATE_BOOL))throw new RuntimeException(ucfirst($environment).' requires SESSION_SECURE=true.');
      if(!filter_var($value('RATE_LIMIT_ENABLED',false),FILTER_VALIDATE_BOOL))throw new RuntimeException(ucfirst($environment).' requires RATE_LIMIT_ENABLED=true.');
      if(strtolower(trim((string)$value('RATE_LIMIT_MODE','report_only')))!=='enforce')throw new RuntimeException(ucfirst($environment).' requires RATE_LIMIT_MODE=enforce.');
      $salt=trim((string)$value('RATE_LIMIT_IDENTITY_SALT',''));
      if($salt===''||in_array(strtolower($salt),['change-me','changeme','secret'],true))throw new RuntimeException(ucfirst($environment).' requires a strong RATE_LIMIT_IDENTITY_SALT.');
  }
}
