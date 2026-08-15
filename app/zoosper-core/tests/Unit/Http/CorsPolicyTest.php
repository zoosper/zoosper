<?php

declare(strict_types=1);
use Zoosper\Core\Http\CorsPolicy;
it('allows only exact configured origins',function():void{$p=new CorsPolicy(true,['https://app.example.test']);expect($p->headersFor('https://app.example.test',true))->toHaveKeys(['Access-Control-Allow-Origin','Access-Control-Allow-Methods','Vary'])->and($p->headersFor('https://evil.example.test',true))->toBe([]);});
it('rejects wildcard credentials',function():void{expect(fn()=>new CorsPolicy(true,['*'],credentials:true))->toThrow(InvalidArgumentException::class);});
