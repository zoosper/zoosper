# Routing, middleware & access control

HTTP routing is module-owned. Admin routes run through a central middleware pipeline; API routes stay comparatively stateless.

## Route discovery

```text
ModuleRegistry (enabled modules)
  -> ModuleRouteLoader
  -> config/admin_routes.php + config/api_routes.php per module
  -> Router
```

Controllers are resolved from module `config/controllers.php` factories registered in the service container.

## Admin route example

```php
<?php

declare(strict_types=1);

use Acme\Blog\Controller\NoteAdminController;

return [
    [
        'method' => 'GET',
        'path' => '/admin/notes',
        'controller' => NoteAdminController::class,
        'action' => 'index',
        'permission' => 'note.manage',
    ],
    [
        'method' => 'POST',
        'path' => '/admin/notes/create',
        'controller' => NoteAdminController::class,
        'action' => 'create',
        'permission' => 'note.manage',
    ],
];
```

`permission` may be a string or an array (OR semantics): the user needs **any** listed permission unless your route documents otherwise.

## API route example

```php
return [
    [
        'method' => 'GET',
        'path' => '/api/v1/content/page',
        'controller' => PageApiController::class,
        'action' => 'show',
        'public' => true,
    ],
];
```

Unknown `/api/*` paths return a JSON 404 with a stable error shape.

## Admin middleware pipeline

Admin routes are wrapped in a PSR-15-style pipeline loaded from module `config/admin_middleware.php` contributions. Core auth module registers, in order:

1. **AuthenticationMiddleware** — session admin user required; enforces route `permission` metadata.
2. **CsrfMiddleware** — validates CSRF on state-changing requests.

API routes are **not** wrapped by this stack.

### CSRF in forms

POST forms must include the session CSRF token field expected by middleware (typically `_csrf_token`). Logout uses `POST /admin/logout` with the same token — do not weaken logout to GET.

## Users, roles & permissions

Administration screens manage:

```text
admin_users
admin_roles
admin_permissions
admin_user_roles
admin_role_permissions
```

Typical permissions:

| Permission | Capability |
|------------|------------|
| `user.manage` | Admin users |
| `role.manage` | Roles and permission matrix |

Permissions attach to roles; users receive one or more roles.

Declare new permissions in module `config/acl.php` and reference them on routes.

## Two-factor authentication (foundation)

Module `zoosper-two-factor` defines schema and services for TOTP enrolment, recovery codes, and challenges. Secrets and OTP values must never be logged. Recovery codes are shown once; stored forms use hashes/ciphertext only.

Full post-login 2FA enforcement remains on the roadmap — foundation tables and services exist for integration.

## Frontend fallback routing

Unmatched non-API paths fall through to `PageController`, which resolves published pages by site and slug (see [Sites, pages & content](sites-pages-and-content.md)).

## Related guides

- [Admin interface](admin-interface.md)
- [Security foundations](security-foundations.md)
