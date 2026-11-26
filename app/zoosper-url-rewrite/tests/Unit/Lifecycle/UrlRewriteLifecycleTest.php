<?php declare(strict_types=1);use Zoosper\UrlRewrite\Lifecycle\UrlRewriteLifecycleCoordinator;use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;function rewriteLifeDb():PDO{$p=new PDO('sqlite::memory:');$p->exec('CREATE TABLE url_rewrites(id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER,request_path TEXT,target_path TEXT,entity_type TEXT,entity_id INTEGER,redirect_type INTEGER,is_active INTEGER,created_at TEXT,updated_at TEXT)');return$p;}it('requires disabled-first permanent deletion',function(){$r=new UrlRewriteRepository(rewriteLifeDb());$id=$r->save(null,1,'old','/new',301);$life=new UrlRewriteLifecycleCoordinator($r);expect($life->deletePermanently($r->findByIdForSite($id,1),1,'a')->successful)->toBeFalse();$life->disable($r->findByIdForSite($id,1),1,'a');expect($life->deletePermanently($r->findByIdForSite($id,1),1,'a')->successful)->toBeTrue()->and($r->findByIdForSite($id,1))->toBeNull();});
it('returns lifecycle details without losing the deletion blocker', function (): void {
    $r = new UrlRewriteRepository(rewriteLifeDb());
    $id = $r->save(null, 1, 'old', '/new', 301);
    $life = new UrlRewriteLifecycleCoordinator($r);
    $blocked = $life->deletePermanently($r->findByIdForSite($id, 1), 1, 'a');

    expect($blocked->successful)->toBeFalse()
        ->and($blocked->operation)->toBe('delete')
        ->and($blocked->urlRewriteId)->toBe($id)
        ->and($blocked->active)->toBeTrue()
        ->and($blocked->blockers)->toBe(['status' => 1]);

    $disabled = $life->disable($r->findByIdForSite($id, 1), 1, 'a');
    expect($disabled->successful)->toBeTrue()
        ->and($disabled->operation)->toBe('disable')
        ->and($disabled->urlRewriteId)->toBe($id)
        ->and($disabled->active)->toBeFalse();
});

it('blocks restoring a rewrite that would create a cycle', function (): void {
    $r = new UrlRewriteRepository(rewriteLifeDb());
    $id = $r->save(null, 1, 'a', '/b', 301, false);
    $r->save(null, 1, 'b', '/a', 301);

    $result = (new UrlRewriteLifecycleCoordinator($r))->restore($r->findByIdForSite($id, 1), 1, 'a');

    expect($result->successful)->toBeFalse()
        ->and($r->findByIdForSite($id, 1)->isActive)->toBeFalse();
});

it('restores a safe rewrite independently of other sites', function (): void {
    $r = new UrlRewriteRepository(rewriteLifeDb());
    $id = $r->save(null, 1, 'a', '/b', 301, false);
    $r->save(null, 2, 'b', '/a', 301);

    $result = (new UrlRewriteLifecycleCoordinator($r))->restore($r->findByIdForSite($id, 1), 1, 'a');

    expect($result->successful)->toBeTrue()
        ->and($r->findByIdForSite($id, 1)->isActive)->toBeTrue();
});

it('blocks restoring a previously stored unsafe target', function (): void {
    $r = new UrlRewriteRepository(rewriteLifeDb());
    $id = $r->save(null, 1, 'a', '/\\evil.example', 301, false);

    $result = (new UrlRewriteLifecycleCoordinator($r))->restore($r->findByIdForSite($id, 1), 1, 'a');

    expect($result->successful)->toBeFalse()
        ->and($r->findByIdForSite($id, 1)->isActive)->toBeFalse();
});

it('does not delete a rewrite restored after it was loaded', function (): void {
    $r = new UrlRewriteRepository(rewriteLifeDb());
    $id = $r->save(null, 1, 'old', '/new', 301, false);
    $stale = $r->findByIdForSite($id, 1);
    $r->changeStatus($id, 1, true);

    $result = (new UrlRewriteLifecycleCoordinator($r))->deletePermanently($stale, 1, 'a');

    expect($result->successful)->toBeFalse()
        ->and($r->findByIdForSite($id, 1)->isActive)->toBeTrue();
});

it('does not report a successful deletion for a missing rewrite', function (): void {
    $r = new UrlRewriteRepository(rewriteLifeDb());
    $id = $r->save(null, 1, 'old', '/new', 301, false);
    $stale = $r->findByIdForSite($id, 1);
    $r->deletePermanently($id, 1);

    $result = (new UrlRewriteLifecycleCoordinator($r))->deletePermanently($stale, 1, 'a');

    expect($result->successful)->toBeFalse();
});










