<?php

declare(strict_types=1);

use Zoosper\Settings\Scope\SettingsScopeSelection;
use Zoosper\Site\Repository\SiteRepository;

function selectorRepository(): SiteRepository
{
    $pdo = new \PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE sites (id INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT, name TEXT, status TEXT, homepage_slug TEXT NULL, host TEXT, base_url TEXT, is_enabled INTEGER, theme_code TEXT, locale TEXT, currency TEXT, website_code TEXT, store_code TEXT, store_view_code TEXT, created_at TEXT, updated_at TEXT)');
    $pdo->exec("INSERT INTO sites (id,code,name,status,homepage_slug,host,base_url,is_enabled,theme_code,locale,currency,website_code,store_code,store_view_code,created_at,updated_at) VALUES (42,'main','Main Site','active',NULL,'example.test','https://example.test',1,'default','en_AU','AUD','web','store','view','now','now')");

    return new SiteRepository($pdo);
}

it('builds default, website, store and site contexts from validated records', function (): void {
    $selector = new SettingsScopeSelection(selectorRepository());

    expect($selector->select('default')['label'])->toBe('Default')
        ->and($selector->select('website', 'web')['context']->websiteCode)->toBe('web')
        ->and($selector->select('store', 'store')['context']->websiteCode)->toBe('web')
        ->and($selector->select('site', '42')['context']->siteId)->toBe(42);
});

it('rejects unsupported types and unknown keys', function (): void {
    $selector = new SettingsScopeSelection(selectorRepository());

    expect(fn () => $selector->select('bad'))->toThrow(\InvalidArgumentException::class)
        ->and(fn () => $selector->select('website', 'missing'))->toThrow(\InvalidArgumentException::class)
        ->and(fn () => $selector->select('site', '999'))->toThrow(\InvalidArgumentException::class);
});

it('keeps Request query parsing behind the HTTP adapter', function (): void {
    $source = file_get_contents(dirname(__DIR__, 5) . '/app/zoosper-settings/src/Scope/SettingsScopeSelection.php');

    expect($source)->toContain('$request->query(\'scope\', \'default\')')
        ->toContain('$request->query(\'scope_key\', \'\')')
        ->not->toContain('$_GET');
});










