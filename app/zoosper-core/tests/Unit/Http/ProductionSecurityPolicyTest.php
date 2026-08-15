<?php

declare(strict_types=1);
use Zoosper\Core\Http\ProductionSecurityPolicy;
it('fails closed for insecure production defaults',function():void{putenv('APP_ENV=production');putenv('SESSION_SECURE=false');putenv('RATE_LIMIT_ENABLED=true');putenv('RATE_LIMIT_MODE=enforce');putenv('RATE_LIMIT_IDENTITY_SALT=strong-random-test-salt');expect(fn()=>ProductionSecurityPolicy::assertEnvironment())->toThrow(RuntimeException::class);putenv('APP_ENV=local');});
