<?php

declare(strict_types=1);
it('audits token lifecycle without secret material',function():void{$root=dirname(__DIR__,3);$source=file_get_contents($root.'/src/Admin/Controller/PersonalAccessTokenAdminController.php');expect($source)->toContain('personal_access_token.issued')->toContain('personal_access_token.revoked')->toContain("'public_id'")->toContain("'scopes'")->not->toContain("'token_hash'")->not->toContain("'authorization'");$view=file_get_contents($root.'/resources/views/admin/access-tokens/index.latte');expect($view)->toContain('cannot be shown again')->not->toContain('tokenHash')->not->toContain('token_hash');});










