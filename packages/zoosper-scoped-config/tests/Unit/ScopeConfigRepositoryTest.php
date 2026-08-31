<?php

declare(strict_types=1);

use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;

/*
 * Phase D1 behavioural tests for the scope-config resolution engine.
 * These prove the fallback order (site -> store -> website -> default)
 * exhaustively, since this is the foundational contract every future
 * /admin/settings feature will rely on.
 */

function makeScopeConfigPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec(
        'CREATE TABLE config_scope_values (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            scope_type TEXT NOT NULL,
            scope_key TEXT,
            config_path TEXT NOT NULL,
            config_value TEXT,
            updated_at TEXT NOT NULL,
            UNIQUE(scope_type, scope_key, config_path)
        )'
    );
    return $pdo;
}

it('returns the provided default when nothing has ever been saved', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    expect($repo->get('general.timezone', ScopeContext::default(), 'UTC'))->toBe('UTC')
        ->and($repo->get('general.timezone', ScopeContext::default()))->toBeNull();
});

it('resolves a DEFAULT-scope value when no more specific context is given', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    $repo->set('general.timezone', ScopeType::Default, null, 'Australia/Sydney');

    expect($repo->get('general.timezone', ScopeContext::default()))->toBe('Australia/Sydney');
});

it('a WEBSITE-scope value overrides the DEFAULT for a matching context', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    $repo->set('general.timezone', ScopeType::Default, null, 'UTC');
    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Sydney');

    $context = new ScopeContext(websiteCode: 'main');

    expect($repo->get('general.timezone', $context))->toBe('Australia/Sydney');
});

it('a STORE-scope value overrides WEBSITE and DEFAULT', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    $repo->set('general.timezone', ScopeType::Default, null, 'UTC');
    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Sydney');
    $repo->set('general.timezone', ScopeType::Store, 'main_store', 'Australia/Melbourne');

    $context = new ScopeContext(storeCode: 'main_store', websiteCode: 'main');

    expect($repo->get('general.timezone', $context))->toBe('Australia/Melbourne');
});

it('a SITE-scope value is the most specific and wins over all others', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    $repo->set('general.timezone', ScopeType::Default, null, 'UTC');
    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Sydney');
    $repo->set('general.timezone', ScopeType::Store, 'main_store', 'Australia/Melbourne');
    $repo->set('general.timezone', ScopeType::Site, '42', 'Australia/Perth');

    $context = new ScopeContext(siteId: 42, storeCode: 'main_store', websiteCode: 'main');

    expect($repo->get('general.timezone', $context))->toBe('Australia/Perth');
});

it('falls back to a LESS specific level when the most specific level has no row', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    // Only WEBSITE is set; a full site+store+website context should still
    // fall through site (nothing) -> store (nothing) -> website (found).
    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Sydney');

    $context = new ScopeContext(siteId: 42, storeCode: 'main_store', websiteCode: 'main');

    expect($repo->get('general.timezone', $context))->toBe('Australia/Sydney');
});

it('a context for a DIFFERENT website/store/site does not see an unrelated override', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    $repo->set('general.timezone', ScopeType::Default, null, 'UTC');
    $repo->set('general.timezone', ScopeType::Site, '42', 'Australia/Perth');

    // Site 99 is unrelated to site 42's override.
    $context = new ScopeContext(siteId: 99);

    expect($repo->get('general.timezone', $context))->toBe('UTC');
});

it('getWithSource() reports which scope level the value came from', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    $repo->set('general.timezone', ScopeType::Default, null, 'UTC');
    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Sydney');

    $context = new ScopeContext(websiteCode: 'main');
    $result = $repo->getWithSource('general.timezone', $context);

    expect($result['value'])->toBe('Australia/Sydney')
        ->and($result['resolvedScope'])->toBe(ScopeType::Website);

    // A context with no override anywhere resolves to the provided default,
    // with a null resolvedScope (nothing was actually found).
    $noOverride = $repo->getWithSource('mail.from_address', ScopeContext::default(), 'noreply@example.test');
    expect($noOverride['value'])->toBe('noreply@example.test')
        ->and($noOverride['resolvedScope'])->toBeNull();
});

it('set() updates an existing value in place rather than duplicating rows', function (): void {
    $pdo = makeScopeConfigPdo();
    $repo = new ScopeConfigRepository($pdo);

    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Sydney');
    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Melbourne');

    $context = new ScopeContext(websiteCode: 'main');
    expect($repo->get('general.timezone', $context))->toBe('Australia/Melbourne');

    $count = (int) $pdo->query('SELECT COUNT(*) FROM config_scope_values')->fetchColumn();
    expect($count)->toBe(1);
});

it('clear() removes an override, falling back to the next level', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    $repo->set('general.timezone', ScopeType::Default, null, 'UTC');
    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Sydney');

    $context = new ScopeContext(websiteCode: 'main');
    expect($repo->get('general.timezone', $context))->toBe('Australia/Sydney');

    $repo->clear('general.timezone', ScopeType::Website, 'main');

    expect($repo->get('general.timezone', $context))->toBe('UTC');
});

it('allForPath() lists every saved override across all scope levels', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    $repo->set('general.timezone', ScopeType::Default, null, 'UTC');
    $repo->set('general.timezone', ScopeType::Website, 'main', 'Australia/Sydney');
    $repo->set('general.timezone', ScopeType::Site, '42', 'Australia/Perth');

    $rows = $repo->allForPath('general.timezone');

    expect($rows)->toHaveCount(3);
    $byScope = [];
    foreach ($rows as $row) {
        $byScope[$row['scopeType']->value] = $row['value'];
    }
    expect($byScope)->toBe([
        'default' => 'UTC',
        'site' => 'Australia/Perth',
        'website' => 'Australia/Sydney',
    ]);
});

it('rejects a non-null scope key for ScopeType::Default', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    expect(fn () => $repo->set('general.timezone', ScopeType::Default, 'oops', 'UTC'))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects a null/empty scope key for a non-default scope type', function (): void {
    $repo = new ScopeConfigRepository(makeScopeConfigPdo());

    expect(fn () => $repo->set('general.timezone', ScopeType::Website, null, 'UTC'))
        ->toThrow(InvalidArgumentException::class);
    expect(fn () => $repo->set('general.timezone', ScopeType::Website, '', 'UTC'))
        ->toThrow(InvalidArgumentException::class);
});

it('ScopeContext::fromSiteArray() builds a correct context from Site-shaped data', function (): void {
    $context = ScopeContext::fromSiteArray([
        'id' => 42,
        'storeCode' => 'main_store',
        'websiteCode' => 'main',
    ]);

    expect($context->siteId)->toBe(42)
        ->and($context->storeCode)->toBe('main_store')
        ->and($context->websiteCode)->toBe('main');
});

it('ScopeContext::fromSiteArray() treats missing/empty fields as absent', function (): void {
    $context = ScopeContext::fromSiteArray(['id' => 42]);

    expect($context->siteId)->toBe(42)
        ->and($context->storeCode)->toBeNull()
        ->and($context->websiteCode)->toBeNull();
});











