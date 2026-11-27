<?php

declare(strict_types=1);

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Message\FlashMessage;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\ScopedConfig\ScopeType;
use Zoosper\Settings\Admin\SettingsAdminUrls;
use Zoosper\Settings\Admin\SettingsCatalogueResponder;
use Zoosper\Settings\Admin\SettingsMutationCoordinator;
use Zoosper\Settings\Audit\SettingsAuditLogger;
use Zoosper\Settings\Catalogue\ModuleSettingsCatalogueLoader;
use Zoosper\Settings\Persistence\ScopeConfigSettingStore;
use Zoosper\Settings\Scope\SettingsScopeSelection;
use Zoosper\Settings\Value\ScopedSettingValueResolver;
use Zoosper\Settings\Value\SettingValueResolver;
use Zoosper\Settings\Write\ScopedSettingClearer;
use Zoosper\Settings\Write\SectionSettingsWriter;
use Zoosper\Settings\Write\SettingValueNormaliser;
use Zoosper\Site\Repository\SiteRepository;

function sectionPermissionFixture(): array
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE sites (id INTEGER PRIMARY KEY, code TEXT, name TEXT, status TEXT, homepage_slug TEXT, host TEXT, base_url TEXT, is_enabled INTEGER, theme_code TEXT, locale TEXT, currency TEXT, website_code TEXT, store_code TEXT, store_view_code TEXT, created_at TEXT, updated_at TEXT)');
    $pdo->exec('CREATE TABLE config_scope_values (id INTEGER PRIMARY KEY AUTOINCREMENT, scope_type TEXT NOT NULL, scope_key TEXT NULL, config_path TEXT NOT NULL, config_value TEXT NULL, updated_at TEXT NOT NULL)');
    $repository = new ScopeConfigRepository($pdo);
    $store = new ScopeConfigSettingStore($pdo, $repository);
    $catalogue = new ModuleSettingsCatalogueLoader(new ModuleRegistry(dirname(__DIR__, 2) . '/Fixtures/permission-catalogue'));
    $values = new ScopedSettingValueResolver($store, new SettingValueResolver(ConfigRepository::fromArray([])));
    $scope = new SettingsScopeSelection(new SiteRepository($pdo));
    $urls = new SettingsAdminUrls();
    $renderer = new class implements AdminViewRendererInterface {
        public array $data = [];
        public function render(string $title, string $template, array $data, ?AdminUser $user, string $active = 'dashboard', ?string $shellTitle = null): string
        {
            $this->data = $data;
            return '';
        }
    };
    $flash = new class implements FlashMessageStoreInterface {
        public function add(FlashMessage $message): void {}
        public function success(string $text, string $key = 'success'): void {}
        public function error(string $text, string $key = 'error'): void {}
        public function warning(string $text, string $key = 'warning'): void {}
        public function info(string $text, string $key = 'info'): void {}
        public function pull(): array { return []; }
        public function peek(): array { return []; }
    };
    return [
        new SettingsCatalogueResponder($renderer, $catalogue, $values, $scope, new CsrfTokenManager(), $urls),
        new SettingsMutationCoordinator($catalogue, $values, $scope, new SectionSettingsWriter($store, new SettingValueNormaliser()), new ScopedSettingClearer($store), new SettingsAuditLogger(null), $flash, $urls),
        $renderer,
        $repository,
    ];
}

function sectionPermissionUser(array $permissions): AdminUser
{
    return new AdminUser(1, 'admin@example.test', 'Admin', '', 'active', $permissions);
}

it('rejects duplicate paths across sections that could bypass section permissions', function (): void {
    $loader = new ModuleSettingsCatalogueLoader(new ModuleRegistry(dirname(__DIR__, 2) . '/Fixtures/duplicate-path'));
    expect(fn () => $loader->load())->toThrow(RuntimeException::class, 'Duplicate setting path');
});

it('only exposes sections and values permitted to the current user', function (): void {
    [$responder, , $renderer, $repository] = sectionPermissionFixture();
    $repository->set('example.restricted', ScopeType::Default, null, 'private');
    $responder->respond(new Request('GET', '/admin/settings'), sectionPermissionUser(['settings.manage']));
    expect($renderer->data['sectionCount'])->toBe(1)
        ->and($renderer->data['effectiveValues'])->not->toHaveKey('example.restricted');
    $responder->respond(new Request('GET', '/admin/settings'), sectionPermissionUser(['settings.manage', 'restricted.manage']));
    expect($renderer->data['sectionCount'])->toBe(2)
        ->and($renderer->data['effectiveValues']['example.restricted']->value)->toBe('private');
});

it('denies forged section saves and clears without the section permission', function (): void {
    [, $mutations, , $repository] = sectionPermissionFixture();
    $repository->set('example.restricted', ScopeType::Default, null, 'original');
    $user = sectionPermissionUser(['settings.manage']);
    $save = new Request('POST', '/admin/settings/save', form: ['section' => 'restricted', 'settings' => ['example.restricted' => 'changed']]);
    $clear = new Request('POST', '/admin/settings/clear', form: ['section' => 'restricted', 'path' => 'example.restricted']);
    expect($mutations->save($save, $user)->statusCode())->toBe(403)
        ->and($repository->get('example.restricted', ScopeContext::default()))->toBe('original')
        ->and($mutations->clear($clear, $user)->statusCode())->toBe(403)
        ->and($repository->get('example.restricted', ScopeContext::default()))->toBe('original');
    $authorized = sectionPermissionUser(['settings.manage', 'restricted.manage']);
    expect($mutations->save($save, $authorized)->statusCode())->toBe(302)
        ->and($repository->get('example.restricted', ScopeContext::default()))->toBe('changed')
        ->and($mutations->clear($clear, $authorized)->statusCode())->toBe(302)
        ->and($repository->get('example.restricted', ScopeContext::default()))->toBeNull();
});