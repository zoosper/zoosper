# Core Decoupling Architecture Test

## Rule

`app/zoosper-core/src` must never depend on a feature module. Dependencies flow
one way: feature modules depend on core interfaces (e.g. `SiteLookupInterface`,
`FallbackHandlerInterface`), never the reverse.

## Enforcement

`CoreDecouplingArchitectureTest` scans core source and fails the suite if any
feature-module namespace appears:

- `Zoosper\Page`, `Zoosper\Site`, `Zoosper\Auth`, `Zoosper\Theme`,
  `Zoosper\Media`, `Zoosper\Admin`, `Zoosper\Api`, `Zoosper\Mail`,
  `Zoosper\TwoFactor`, `Zoosper\UrlRewrite`.

Two checks run: a broad substring scan and a stricter `use` import ban.

## If it fails

You have introduced core->feature coupling. Fix it by:

1. Defining a core-owned interface for what core needs.
2. Implementing that interface in the feature module.
3. Binding the implementation in the feature module's `config/services.php`.

This is exactly how the site-lookup and fallback-handler seams were built.

## Relationship to the audit script

`tools/audit-architecture-foundation-gates.php` performs a similar scan for ops
reporting. This Pest test is the ENFORCED guard - it runs in the suite and, via
`tools/gate.php`, in CI.
