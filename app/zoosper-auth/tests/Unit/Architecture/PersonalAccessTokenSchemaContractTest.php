<?php

declare(strict_types=1);
it('owns indexed hash-only PAT persistence with cascading identity ownership',function():void{$schema=require dirname(__DIR__,3).'/config/db_schema.php';$t=$schema['tables']['personal_access_tokens'];expect($t['columns'])->toHaveKeys(['public_id','admin_user_id','name','token_hash','scopes_json','expires_at','last_used_at','revoked_at'])->and($t['columns'])->not->toHaveKey('token')->and($t['foreign_keys']['fk_personal_access_tokens_owner']['on_delete'])->toBe('CASCADE')->and($t['indexes']['uniq_personal_access_tokens_public_id']['unique'])->toBeTrue();});
