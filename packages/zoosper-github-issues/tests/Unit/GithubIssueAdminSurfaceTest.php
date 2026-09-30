<?php
declare(strict_types=1);
namespace Zoosper\GithubIssues\Tests\Unit;
use Zoosper\GithubIssues\Admin\GithubIssueAdminController;
it('contributes no runtime surface while disabled', function (): void {
    putenv('GITHUB_ISSUES_ENABLED'); unset($_ENV['GITHUB_ISSUES_ENABLED']);
    $root=dirname(__DIR__,4); expect(require $root.'/packages/zoosper-github-issues/config/admin_routes.php')->toBe([])
        ->and(require $root.'/packages/zoosper-github-issues/config/admin_menu.php')->toBe([])
        ->and(require $root.'/packages/zoosper-github-issues/config/controllers.php')->toBe([]);
});
it('declares a protected read-only Admin destination when enabled', function (): void {
    foreach(['GITHUB_ISSUES_ENABLED'=>'true','GITHUB_ISSUES_OWNER'=>'github','GITHUB_ISSUES_REPOSITORY'=>'docs'] as $k=>$v){$_ENV[$k]=$v;putenv($k.'='.$v);} try{$root=dirname(__DIR__,4);$routes=require $root.'/packages/zoosper-github-issues/config/admin_routes.php';expect($routes)->toBe([['method'=>'GET','path'=>'/admin/github-issues','controller'=>GithubIssueAdminController::class,'action'=>'index','permission'=>'github_issue.view']]);}finally{foreach(['GITHUB_ISSUES_ENABLED','GITHUB_ISSUES_OWNER','GITHUB_ISSUES_REPOSITORY'] as $k){unset($_ENV[$k]);putenv($k);}}
});
it('keeps the Admin surface read-only cursor-aware and export-free', function (): void {
    $root=dirname(__DIR__,4);$source=(string)file_get_contents($root.'/packages/zoosper-github-issues/src/Admin/GithubIssueAdminController.php');
    expect($source)->toContain("rel=\"prev\"")->toContain("rel=\"next\"")->toContain('queryParams()')->not->toContain('export')->not->toContain('$_GET')->not->toContain('$_POST');
});
