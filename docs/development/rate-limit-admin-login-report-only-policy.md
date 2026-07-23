# Admin Login Report-only Rate Limit Policy

Phase 1.39at-ax prepares the first concrete rate-limit policy target: admin login.

## Source evidence from hook readiness

The admin hook discovery and planning reports showed:

```text
app/zoosper-auth/config/admin_middleware.php exists
contains_return_array: yes
contains_authentication_middleware: yes
contains_csrf: yes
contains_rate_limit: no
Pattern: return-array-with-authentication-middleware
Can patch later: yes
Errors: 0
```

This means the admin middleware hook is suitable for a future source-specific patch, while still requiring careful ordering around authentication and CSRF middleware.

## Strategy

Before mutating the live middleware configuration, define the first policy and context factory:

```text
admin.login
```

The policy remains safe because global rate limiting stays disabled by default:

```php
'enabled' => false,
'mode' => 'report_only',
```

## Default admin.login policy

The guarded patch tool adds this policy shape if it is missing:

```php
'admin.login' => [
    'scope' => 'admin',
    'max_attempts' => 5,
    'window_seconds' => 300,
],
```

Because `enabled` remains `false`, adding the policy does not affect runtime behaviour until report-only mode is explicitly enabled.

## Context factory

`AdminRateLimitContextFactory` creates `RateLimitContext` objects from a route key and safe identity parts. It uses `RateLimitIdentityHasher` and the configured identity salt so raw identity values are not persisted.

## Next phase

After this policy foundation is green, the next phase can add the source-specific disabled-by-default report-only middleware hook into `app/zoosper-auth/config/admin_middleware.php`.
