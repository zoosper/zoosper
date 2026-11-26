<?php

declare(strict_types=1);

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\Token\BearerPrincipalContext;
use Zoosper\Auth\Token\PersonalAccessToken;
use Zoosper\Auth\Token\PersonalAccessTokenPrincipal;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Site\SiteContext;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Menu\Admin\Controller\MenuAdminController;
use Zoosper\Menu\Admin\MenuAdminResponder;
use Zoosper\Menu\Api\MenuApiController;
use Zoosper\Menu\Application\MenuAdminService;
use Zoosper\Menu\Application\MenuItemDeletionService;
use Zoosper\Menu\Application\MenuMutationGuard;
use Zoosper\Menu\Contract\MenuAdminRepositoryInterface;
use Zoosper\Menu\Contract\MenuProviderInterface;
use Zoosper\Menu\Lifecycle\MenuLifecycleCoordinator;
use Zoosper\Menu\Lifecycle\MenuReferenceInspector;
use Zoosper\Menu\Model\Menu;

it('deletes an item exactly once with or without the optional deletion service', function (bool $guarded): void {
    $pdo = new PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE menu_items (id INTEGER, menu_id INTEGER, parent_id INTEGER)');
    $pdo->exec('INSERT INTO menu_items VALUES (4, 3, NULL)');
    $repo = $this->createMock(MenuAdminRepositoryInterface::class);
    $repo->expects($this->once())->method('deleteItem')->with(4);
    $guard = new SessionGuard((new ReflectionClass(AdminUserRepository::class))->newInstanceWithoutConstructor());
    $guard->login(new AdminUser(1, 'admin@example.test', 'Admin', 'hash', 'active', []));
    $controller = new MenuAdminController(
        $guard,
        (new ReflectionClass(MenuAdminResponder::class))->newInstanceWithoutConstructor(),
        new MenuAdminService($repo),
        $repo,
        new AdminUrlGenerator(ConfigRepository::fromArray([])),
        itemDeletion: $guarded ? new MenuItemDeletionService($repo, new MenuReferenceInspector($pdo)) : null,
    );
    $response = $controller->deleteItem((new Request('POST', '/'))->withRouteParams(['id' => 3, 'itemId' => 4]));
    expect($response->statusCode())->toBe(302);
})->with([true, false]);

it('handles menu reloads after mutations without crossing sites', function (string $operation, string $reload): void {
    $pdo = new PDO('sqlite::memory:');
    $menu = new Menu(3, 2, 'main', 'Main', 'inactive', '', '');
    $repo = $this->createMock(MenuAdminRepositoryInterface::class);
    $reloaded = match ($reload) {
        'missing' => null,
        'other site' => new Menu(3, 5, 'main', 'Main', 'inactive', '', ''),
        'same site' => $menu,
    };
    $repo->method('find')->willReturnOnConsecutiveCalls($menu, $reloaded);
    $repo->method('saveMenu')->willReturn(3);
    $references = new MenuReferenceInspector($pdo);
    $principals = new BearerPrincipalContext();
    $request = new Request('POST', '/', siteContext: new SiteContext('', '', '', '', '', '', '', '', '', siteId: 2), routeParams: ['id' => '3']);
    $principals->establish($request, new PersonalAccessTokenPrincipal(
        new PersonalAccessToken(1, 'public', 1, 'test', 'hash', [], null, null, null, ''),
        new AdminUser(1, 'admin@example.test', 'Admin', 'hash', 'active', []),
    ));
    $controller = new MenuApiController(new JsonResponder(), $principals, $repo, $this->createMock(MenuProviderInterface::class),
        new MenuAdminService($repo), new MenuMutationGuard($pdo, $repo), new MenuItemDeletionService($repo, $references),
        new MenuLifecycleCoordinator($pdo, $references));
    $response = $controller->{$operation}($request);
    $body = json_decode($response->body(), true);
    if ($reload === 'same site') {
        expect($response->statusCode())->toBe(200)->and($body['data']['menu']['id'])->toBe(3);
    } else {
        expect($response->statusCode())->toBe(404)->and($body['error']['code'])->toBe('menu_not_found');
    }
})->with(['update', 'disable'])->with(['missing', 'other site', 'same site']);