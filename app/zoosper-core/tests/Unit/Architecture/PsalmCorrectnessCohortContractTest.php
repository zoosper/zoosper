<?php

declare(strict_types=1);

it('locks the first high-signal Psalm correctness cohort', function (): void {
    $root = dirname(__DIR__, 5);
    $users = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Controller/UserAdminController.php');
    $roles = (string) file_get_contents($root . '/app/zoosper-auth/src/Admin/Controller/RoleAdminController.php');
    $lifecycle = (string) file_get_contents($root . '/app/zoosper-page/src/Api/PageLifecycleApiResponder.php');
    $rewrites = (string) file_get_contents($root . '/app/zoosper-url-rewrite/src/Routing/UrlRewriteFallbackHandler.php');

    expect($users)
        ->toContain('use ($form, $user, $password, $roleIds, $actor): void')
        ->and($roles)
        ->toContain("dirname(__DIR__, 3) . '/config/acl.php'")
        ->not->toContain("/zoosper-auth/config/acl.php")
        ->and($lifecycle)
        ->toContain('$page?->status ?? $result->newStatus')
        ->not->toContain('$result->currentStatus')
        ->and($rewrites)
        ->toContain('$siteContext=$request->siteContext();if($siteContext===null||$siteContext->siteId===null)')
        ->toContain('$this->resolver->resolve($siteContext->siteId,$request->path())');
});
