<?php

declare(strict_types=1);

use Zoosper\Core\Container\ServiceContainer;
use Zoosper\UrlRewrite\Application\UrlRewriteMutationService;
use Zoosper\UrlRewrite\Lifecycle\UrlRewriteLifecycleCoordinator;
use Zoosper\UrlRewrite\Service\UrlRewriteResolver;

it('wires rewrite services to the same repository and enforces chain validation', function (): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('CREATE TABLE url_rewrites(id INTEGER PRIMARY KEY AUTOINCREMENT,site_id INTEGER,request_path TEXT,target_path TEXT,entity_type TEXT,entity_id INTEGER,redirect_type INTEGER,is_active INTEGER,created_at TEXT,updated_at TEXT)');

    $services = new ServiceContainer();
    $services->set(PDO::class, $pdo);
    foreach (require __DIR__ . '/../../../config/services.php' as $id => $factory) {
        $services->factory($id, $factory);
    }

    $mutations = $services->get(UrlRewriteMutationService::class);
    $resolver = $services->get(UrlRewriteResolver::class);
    $lifecycle = $services->get(UrlRewriteLifecycleCoordinator::class);
    $rewrite = $mutations->save(1, null, ['request_path' => '/old', 'target_path' => '/new'], 1, 'admin');

    expect($resolver->resolve(1, '/old')?->id)->toBe($rewrite->id)
        ->and($resolver->resolve(2, '/old'))->toBeNull()
        ->and(fn () => $mutations->save(1, null, ['request_path' => '/new', 'target_path' => '/old'], 1, 'admin'))
        ->toThrow(InvalidArgumentException::class, 'Redirect cycle detected');

    $result = $lifecycle->disable($rewrite, 1, 'admin');
    expect($result->successful)->toBeTrue()
        ->and($result->urlRewriteId)->toBe($rewrite->id)
        ->and($resolver->resolve(1, '/old'))->toBeNull();
});