<?php
declare(strict_types=1);
use Zoosper\Core\Http\ProductionSecurityPolicy;
afterEach(function():void{foreach(['APP_ENV','APP_DEBUG','SESSION_SECURE','RATE_LIMIT_ENABLED','RATE_LIMIT_MODE','RATE_LIMIT_IDENTITY_SALT','TWO_FACTOR_ENCRYPTION_KEY','DB_DRIVER','DB_CONNECTION'] as $key){unset($_ENV[$key]);putenv($key);}putenv('APP_ENV=testing');});
it('rejects empty and unknown environments',function():void{unset($_ENV['APP_ENV']);putenv('APP_ENV');expect(fn()=>ProductionSecurityPolicy::assertEnvironment())->toThrow(RuntimeException::class);putenv('APP_ENV=prod');expect(fn()=>ProductionSecurityPolicy::assertEnvironment())->toThrow(RuntimeException::class);});
it('enforces strict controls for staging as well as production',function():void{foreach(['staging','production'] as $env){putenv('APP_ENV='.$env);putenv('SESSION_SECURE=false');expect(fn()=>ProductionSecurityPolicy::assertEnvironment())->toThrow(RuntimeException::class);}});
it('permits recognised non-public development environments',function():void{foreach(['local','development','testing'] as $env){putenv('APP_ENV='.$env);ProductionSecurityPolicy::assertEnvironment();expect(true)->toBeTrue();}});










