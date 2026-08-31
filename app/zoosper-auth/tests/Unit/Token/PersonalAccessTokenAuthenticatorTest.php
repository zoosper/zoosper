<?php

declare(strict_types=1);
it('uses strict bearer format hash comparison revocation expiry and active-owner checks',function():void{$s=(string)file_get_contents(dirname(__DIR__,3).'/src/Token/PersonalAccessTokenAuthenticator.php');expect($s)->toContain("/^Bearer (zp_pat_")->toContain('hash_equals(')->toContain('isRevoked()')->toContain('isExpired()')->toContain("status!=='active'")->not->toContain('password_verify(');});










