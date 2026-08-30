<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Api;

use PDO;
use Zoosper\Auth\Api\RoleApiController;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenRepository;
use Zoosper\Auth\Token\PersonalAccessTokenService;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;

beforeEach(function (): void {
    $this->pdo = new PDO('sqlite::memory:');
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $this->pdo->exec('CREATE TABLE admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        password_hash TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT "active",
        created_at TEXT,
        updated_at TEXT
    )');

    $this->pdo->exec('CREATE TABLE admin_roles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        label TEXT NOT NULL,
        created_at TEXT,
        updated_at TEXT
    )');

    $this->pdo->exec('CREATE TABLE admin_permissions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        label TEXT NOT NULL,
        parent_code TEXT,
        sort_order INTEGER DEFAULT 100
    )');

    $this->pdo->exec('CREATE TABLE admin_role_permissions (
        role_id INTEGER NOT NULL,
        permission_id INTEGER NOT NULL,
        PRIMARY KEY (role_id, permission_id)
    )');

    $this->pdo->exec('CREATE TABLE admin_user_roles (
        user_id INTEGER NOT NULL,
        role_id INTEGER NOT NULL,
        PRIMARY KEY (user_id, role_id)
    )');

    $this->pdo->exec('CREATE TABLE personal_access_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        public_id TEXT NOT NULL,
        admin_user_id INTEGER NOT NULL,
        name TEXT NOT NULL,
        token_hash TEXT NOT NULL,
        scopes_json TEXT NOT NULL,
        expires_at TEXT NULL,
        last_used_at TEXT NULL,
        revoked_at TEXT NULL,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )');

    // Seed permissions
    $this->pdo->exec('INSERT INTO admin_permissions (id, code, label, parent_code, sort_order) VALUES
        (1, "admin.access", "Admin Access", NULL, 10),
        (2, "role.view", "View Roles", "users", 20),
        (3, "role.manage", "Manage Roles", "users", 30)');

    // Seed admin user
    $this->pdo->exec('INSERT INTO admin_users (id, name, email, password_hash, status) VALUES
        (1, "Super Admin", "admin@zoosper.test", "hash", "active")');

    // Seed admin role & assign
    $this->pdo->exec('INSERT INTO admin_roles (id, code, label) VALUES (1, "super_admin", "Super Admin")');
    $this->pdo->exec('INSERT INTO admin_role_permissions (role_id, permission_id) VALUES (1, 1), (1, 2), (1, 3)');
    $this->pdo->exec('INSERT INTO admin_user_roles (user_id, role_id) VALUES (1, 1)');

    $this->userRepo = new AdminUserRepository($this->pdo);
    $this->roleRepo = new RoleRepository($this->pdo);
    $this->tokenRepo = new PersonalAccessTokenRepository($this->pdo);
    $this->tokenService = new PersonalAccessTokenService($this->tokenRepo);
    $this->auth = new PersonalAccessTokenAuthenticator($this->tokenRepo, $this->userRepo);
    $this->controller = new RoleApiController(new JsonResponder(), $this->auth, $this->roleRepo);
});

it('lists roles with valid token and permission', function (): void {
    $issued = $this->tokenService->issue(1, 'API Token', ['roles:read']);
    $request = new Request('GET', '/api/v1/roles', headers: ['authorization' => 'Bearer ' . $issued['token']]);

    $response = $this->controller->index($request);
    expect($response->statusCode())->toBe(200);

    $body = json_decode($response->body(), true);
    expect($body['success'])->toBeTrue()
        ->and($body['data']['roles'])->toHaveCount(1)
        ->and($body['data']['roles'][0]['code'])->toBe('super_admin');
});

it('rejects unauthenticated requests', function (): void {
    $request = new Request('GET', '/api/v1/roles');
    $response = $this->controller->index($request);
    expect($response->statusCode())->toBe(401);
});

it('rejects requests with missing required scope', function (): void {
    $issued = $this->tokenService->issue(1, 'API Token', ['pages:read']);
    $request = new Request('GET', '/api/v1/roles', headers: ['authorization' => 'Bearer ' . $issued['token']]);

    $response = $this->controller->index($request);
    expect($response->statusCode())->toBe(403);
});

it('creates, reads, updates, and deletes a role via API', function (): void {
    $issued = $this->tokenService->issue(1, 'Write Token', ['roles:read', 'roles:write']);

    // Create
    $createReq = new Request(
        'POST',
        '/api/v1/roles',
        headers: ['authorization' => 'Bearer ' . $issued['token'], 'content-type' => 'application/json'],
        body: json_encode(['code' => 'editor', 'label' => 'Editor Role', 'permission_ids' => [1, 2]]),
    );
    $createRes = $this->controller->create($createReq);
    expect($createRes->statusCode())->toBe(201);
    $created = json_decode($createRes->body(), true)['data']['role'];
    expect($created['code'])->toBe('editor')
        ->and($created['permission_ids'])->toEqual([1, 2]);

    $roleId = (int) $created['id'];

    // Show
    $showReq = (new Request('GET', '/api/v1/roles/' . $roleId, headers: ['authorization' => 'Bearer ' . $issued['token']]))
        ->withRouteParams(['id' => (string) $roleId]);
    $showRes = $this->controller->show($showReq);
    expect($showRes->statusCode())->toBe(200);

    // Update
    $updateReq = (new Request(
        'PATCH',
        '/api/v1/roles/' . $roleId,
        headers: ['authorization' => 'Bearer ' . $issued['token'], 'content-type' => 'application/json'],
        body: json_encode(['label' => 'Senior Editor', 'permission_ids' => [1]]),
    ))->withRouteParams(['id' => (string) $roleId]);
    $updateRes = $this->controller->update($updateReq);
    expect($updateRes->statusCode())->toBe(200);
    $updated = json_decode($updateRes->body(), true)['data']['role'];
    expect($updated['label'])->toBe('Senior Editor')
        ->and($updated['permission_ids'])->toEqual([1]);

    // Delete
    $deleteReq = (new Request('DELETE', '/api/v1/roles/' . $roleId, headers: ['authorization' => 'Bearer ' . $issued['token']]))
        ->withRouteParams(['id' => (string) $roleId]);
    $deleteRes = $this->controller->delete($deleteReq);
    expect($deleteRes->statusCode())->toBe(200);
    expect(json_decode($deleteRes->body(), true)['data']['deleted'])->toBeTrue();

    // Verify deleted
    $verifyReq = (new Request('GET', '/api/v1/roles/' . $roleId, headers: ['authorization' => 'Bearer ' . $issued['token']]))
        ->withRouteParams(['id' => (string) $roleId]);
    $verifyRes = $this->controller->show($verifyReq);
    expect($verifyRes->statusCode())->toBe(404);
});
