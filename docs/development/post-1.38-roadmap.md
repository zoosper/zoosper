# Post-1.38 Roadmap

Phase 1.38 closed the role-admin controller cleanup arc by moving confirmed role-admin markup owners out of `RoleAdminController` and into view partials.

## Current confirmed state

- Role admin controller inline markup has been moved into dedicated view partials.
- `RoleAdminController` now retains request handling, repository interactions, CSRF token preparation, redirects, and audit logging.
- Role admin views own table/form/checkbox markup.
- The closeout audit path should include Pest, `audit-role-admin-view-ownership.php`, and `audit-role-admin-latte-closeout.php --enforce-closed`.

## Recommended next sequence

### Phase 1.39 — Rate limiting foundation
Implement database-backed rate limit storage and policy wiring. This has stronger platform value than immediately polishing the role permission tree UI.

### Phase 1.40 — Configuration layering
Add module-supplied default configuration with root-level overrides. This unlocks safer package extraction later, including role-admin extraction.

### Phase 1.41 — Method plugin/interceptor foundation
Introduce a controlled interception system for extension points that should not require controller forks.

### Phase 1.42 — Permission tree UX upgrade
Improve admin role permission assignment UI with hierarchical, collapsible grouped permissions and optional search/filter behaviour.

### Phase 1.43 — Admin roles package extraction evaluation
Revisit whether role-admin should become a package such as `zoosper-admin-roles` after config layering and extension seams exist.

## Why not extract admin roles immediately?

Role admin depends on auth, ACL, repositories, admin layout, CSRF, audit logging, and user assignment. Extracting it before config layering and extension seams are stable risks creating another tight package that still depends heavily on core internals.

The better order is:

1. keep role-admin clean and view-backed now;
2. complete platform/package infrastructure;
3. extract role-admin only when module defaults, service overrides, assets, routes, ACL, and migrations can be package-owned cleanly.

## Next best implementation phase

The next implementation phase should be Phase 1.39: rate limiting foundation backed by database storage.
