# Marko Adoption Matrix

## Status

Accepted as the architecture source of truth for framework ownership.

## Purpose

Zoosper is a CMS product built on Marko. It is not a competing PHP framework.
This matrix prevents Zoosper from creating or retaining generic framework
infrastructure without first evaluating the equivalent Marko package.

## Decision labels

- **ADOPT**: Marko owns the generic capability. Zoosper keeps only CMS policy.
- **ADAPT**: Use a narrow Zoosper adapter around a Marko contract while migration is incomplete.
- **KEEP**: Zoosper owns the capability because it is CMS domain behaviour.
- **DEFER**: Do not change until the higher-risk behaviour has been compared and protected by tests.
- **REMOVE**: Delete the duplicate Zoosper implementation after migration.

## Non-negotiable boundary

- Marko owns reusable framework mechanics.
- Zoosper owns CMS domain models, policies, workflows and presentation.
- Feature modules depend on contracts, not infrastructure drivers.
- There must not be two active implementations of the same framework capability.
- A replacement phase is incomplete until the superseded Zoosper path is removed.

## Current installed Marko baseline

The locked repository snapshot contains Marko 0.8.5 packages for cache,
file cache, Redis cache, configuration, core, encryption, errors and simple
error display. Any additional Marko package must be added deliberately and
locked through Composer before adoption.

## Capability decisions

| Capability | Current Zoosper evidence | Decision | Target owner | Required next action |
| --- | --- | --- | --- | --- |
| Error contracts and formatting | `zoosper/errors` already composes Marko errors and extends `MarkoException` | ADAPT | Marko mechanics, Zoosper CMS error context | Retain the adapter boundary; remove any duplicate generic formatting only after parity tests |
| Configuration contracts | `MarkoConfigRepositoryAdapter` already implements Marko's repository contract | ADAPT, then ADOPT | Marko | Make the Marko contract canonical; preserve Zoosper layering only as CMS source contribution until Marko-native loading replaces it |
| Configuration loading | `ConfigRepository`, `ModuleConfigAggregator`, layered loaders and separate CLI root loading coexist | ADOPT | Marko | First implementation candidate: one HTTP and CLI path, then remove root-only CLI loading |
| Environment loading | Global helper and multiple loaders are recorded in the codebase | DEFER | Marko if API parity is proven | Inventory every consumer and secret-loading rule before replacement |
| Module discovery and priority | `ModuleRegistry` scans several homes and silently deduplicates identities | ADOPT | Marko | Adopt one Composer-native module identity and explicit layer priority; make same-layer conflicts loud |
| Dependency injection | `ServiceContainer` and service provider loaders exist in core | DEFER, likely ADOPT | Marko | Compare factories, aliases, lazy services, diagnostics and circular dependency handling |
| Preferences | Zoosper uses manual interfaces and bindings | ADOPT | Marko | Use Marko preference resolution once container ownership moves |
| Plugins and interception | A large report-only method-plugin subsystem exists in core | DEFER, likely ADOPT | Marko | Protect ordering, type, exception and conflict behaviour before deleting the Zoosper engine |
| Events and observers | Zoosper owns event dispatch infrastructure and CMS domain events | ADAPT | Marko runtime, Zoosper event classes | Move dispatch mechanics to Marko while retaining CMS event definitions |
| CLI runtime | `bin/zoosper` constructs configuration, modules, services, logging and PDO itself | ADOPT | Marko | Evaluate and add `marko/cli`; command dependencies must resolve lazily |
| Routing and middleware | Zoosper has a working router, middleware, permissions and fallback handling | DEFER | Marko mechanics, Zoosper CMS resolution | Compare route matching, conflicts, middleware and fallback extension before migration |
| Cache contracts and drivers | Marko cache, file and Redis packages are installed while Zoosper retains core cache classes | ADOPT | Marko | Use Marko contracts and drivers; keep only CMS cache keys, tags, variation and invalidation policy |
| Full-page HTTP caching | Zoosper HTTP cache policy exists but is not fully wired | DEFER | Marko page-cache mechanics, Zoosper policy | Evaluate Marko page-cache packages before wiring or extending Zoosper code |
| Logging | Zoosper `LogManager` and module-owned logging exist | DEFER, likely ADOPT | Marko mechanics, Zoosper channels and redaction | Evaluate Marko log packages before another Zoosper logger extraction |
| Filesystem | Storage behaviour is spread through feature implementations | DEFER, likely ADOPT | Marko | Inventory local and future object-storage requirements first |
| Views and Latte | Zoosper has Latte adapters and CMS template override rules | DEFER | Marko contracts, Zoosper CMS lookup policy | Preserve templates and override precedence while comparing Marko view packages |
| Session | Zoosper session and admin security behaviour are live | DEFER | Marko mechanics, Zoosper security policy | Migrate only after fixation, rotation, cookie and request-scope tests exist |
| Authentication | Admin identity, login, 2FA, audit and recovery are Zoosper domain behaviour | KEEP plus possible ADAPT | Zoosper domain on Marko contracts | Do not replace the domain workflow; evaluate guard contracts separately |
| Authorisation | Zoosper ACL and route permission semantics are live and security-sensitive | KEEP plus possible ADAPT | Zoosper policy on Marko contracts | Preserve OR semantics and role boundaries; evaluate Marko policy infrastructure later |
| Hashing and encryption | Security behaviour is live; Marko encryption 0.8.5 is installed | DEFER | Marko contract where parity is proven | Do not migrate secrets or password behaviour without compatibility and rotation tests |
| Database connections | Zoosper uses PDO, MySQL and SQLite with hardened schema behaviour | KEEP for now | Zoosper | Separate lazy connection lifecycle from any future persistence rewrite |
| Read/write database routing | High-traffic planning requires replica support | DEFER | Likely Marko decorator | Evaluate actual transaction pinning and read-after-write behaviour first |
| Schema and migrations | Zoosper owns declarative schema, module migrations and SQLite tests | KEEP | Zoosper CMS infrastructure | Do not adopt a different entity mapper as part of framework reconciliation |
| Validation and pagination | Generic helpers exist in Zoosper | DEFER, likely ADOPT | Marko | Compare behaviour before replacing; retain CMS validation rules |
| Mail transport | Zoosper mail and diagnostics are product-integrated | DEFER | Marko transport, Zoosper mail policy | Compare drivers, logging and diagnostics before migration |
| Media transforms | Zoosper media library is CMS domain; generic transforms are infrastructure | ADAPT | Zoosper library on Marko media contracts | Keep asset metadata and policy; evaluate Marko GD/Imagick drivers later |
| Translation | Zoosper owns module translation aggregation and admin locale behaviour | DEFER | Marko mechanics, Zoosper catalogues and locale policy | Preserve fallback and module override behaviour during evaluation |

## Adoption order

1. Configuration ownership and HTTP/CLI parity.
2. CLI ownership and lazy dependency resolution.
3. Canonical Marko module identity and conflict rules.
4. Cache contracts and drivers.
5. Container, preferences, events and plugins.
6. Routing, views and generic HTTP infrastructure.
7. Security-sensitive session, authentication and authorisation infrastructure.
8. Optional infrastructure such as logging, filesystem, mail and media transforms.
9. Database read/write routing only after connection lifecycle is clean.

## Exit criteria for every adoption phase

- Real Marko source and tests have been inspected for the locked version.
- Behavioural tests capture the Zoosper behaviour being preserved.
- CMS policy is separated from generic mechanics.
- Feature modules depend on contracts.
- The replacement has one runtime path.
- Duplicate Zoosper infrastructure is deleted.
- Documentation and package boundaries are updated.
