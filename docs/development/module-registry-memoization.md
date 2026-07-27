# ModuleRegistry Memoization

## Problem

`enabledModules()` re-globbed 5 filesystem patterns and re-parsed every
`module.php` / vendor `composer.json` on every call, with no caching. Because
core loaders (services, controllers, routes, events, entity-save listeners, admin
menu, translations, admin assets) each call it independently, a single admin page
render triggered multiple full re-scans (Sonnet Phase 2 §2.2/§3).

## Fix

`ModuleRegistry` caches its own computed module list per instance. Since the
registry is constructed once per `ApplicationFactory::create()` and shared by
every loader, the cache is effectively request-scoped: the first caller pays the
filesystem cost, everyone else reads the cached list.

## API

- `enabledModules(): list<Module>` — unchanged signature/behaviour; now cached.
- `clearCache(): void` — forces the next call to re-scan. Not needed in normal
  PHP-FPM/CLI request handling; relevant only for long-lived worker processes or
  tests that must observe a changed module set within one registry instance.

## Safety

Discovery is a pure filesystem read (no side effects), and module state cannot
change mid-request, so per-instance memoization is safe by construction.
