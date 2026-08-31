<?php

declare(strict_types=1);
it('declares authenticated POST-only self-owned token mutations',function():void{$root=dirname(__DIR__,3);$routes=require $root.'/config/admin_routes.php';$map=[];foreach($routes as $r)$map[$r['method'].' '.$r['path']]=$r;expect($map)->toHaveKeys(['GET /admin/access-tokens','POST /admin/access-tokens/create','POST /admin/access-tokens/{id:\d+}/revoke']);$source=file_get_contents($root.'/src/Admin/Controller/PersonalAccessTokenAdminController.php');$view=file_get_contents($root.'/resources/views/admin/access-tokens/index.latte');expect($source)->toContain('allForUser($user->id)')->toContain('revoke($id, $user->id')->not->toContain('token_hash')->and($view)->toContain('name="_csrf_token"')->toContain('method="post"');});










