# Zoosper CMS - Testing Guide (co-located edition)

**Introduced in:** Phase 1.21 - Pest Test Harness Foundation.

Pest is the **primary correctness gate** for the `zoosper/zoosper` dev branch.
Tests are **co-located inside each module** so modules stay self-contained.

---

## Running the tests

```bash
composer test            # run the whole suite (all modules)
composer test:unit       # only the Unit suite (fast, isolated)
composer test:feature    # only the Feature suite
composer test:coverage   # run with coverage, fail under the configured minimum
```

---

## The one rule

> **Every behavioural change ships with a Pest test.**
> No exceptions, no bespoke one-off verifier scripts.

Fixed a bug? Add a regression test that fails without your fix and passes with it.
Added behaviour? Add a test that proves it.

---

## Where tests live now

Tests are **NOT** in a root `tests/` folder. Each module owns its own tests:

```
app/zoosper-core/
├── src/
│   └── Testing/TestCase.php          <- shared base (autoloaded via existing PSR-4)
└── tests/
    ├── Pest.php                       <- module's Pest binding
    └── Unit/Entity/Save/FieldDefinitionRegistryTest.php

app/zoosper-page/
└── tests/
    ├── Pest.php
    └── Unit/... / Feature/...
```

The globbed `phpunit.xml` discovers `app/*/tests/{Unit,Feature}` and
`modules/*/tests/{Unit,Feature}` automatically - the same folder-based
discipline as `ModuleRegistry`. Drop a module folder in and its tests come with
it; delete the folder and its tests go too.

| Suite       | Location (per module)            | Use it for                                                  |
|-------------|----------------------------------|-------------------------------------------------------------|
| **Unit**    | `<module>/tests/Unit`            | Services, registries, validators, the save pipeline. No HTTP.|
| **Feature** | `<module>/tests/Feature`         | Composed behaviour: admin route gates (CSRF/ACL), rendering. |

---

## The shared base case

`Zoosper\Core\Testing\TestCase` lives in `app/zoosper-core/src/Testing/` and is
already covered by the existing `Zoosper\Core\` PSR-4 rule - no extra config for
the base itself. It boots a minimal `ServiceContainer` (via `new ServiceContainer()`)
**without** an HTTP request. Resolve services with `$this->service(Foo::class)`
and register fakes with `$this->fakeService(Foo::class, $stub)`.

---

## Adding tests to a module (one-time wiring)

Each module needs exactly **one** `autoload-dev` PSR-4 line mapping its test
namespace to its `tests/` folder, e.g. in the root `composer.json`:

```json
{
  "autoload-dev": {
    "psr-4": {
      "Zoosper\\Core\\Tests\\": "app/zoosper-core/tests/",
      "Zoosper\\Page\\Tests\\": "app/zoosper-page/tests/"
    }
  }
}
```

Then `composer dump-autoload` and write tests under `<module>/tests/Unit` or
`<module>/tests/Feature`.

---

## The isolation principle

Unit tests resolve services from a fresh `ServiceContainer` (via the base
`TestCase`), **not** the full request pipeline. This keeps the suite fast and
makes each module independently testable - a core "true modular CMS" requirement.

---

## Third-party / installed modules

Installed third-party module tests are **not run by default** in a host site's
suite (you don't want to run every dependency's tests in CI). Module *authors*
run the tests while developing the module; *consumers* generally don't.

---

## Regression tests replace verifier scripts

Writing a bespoke `tools/verify-*.php` for a single just-introduced bug is
replaced by a real Pest regression test. See
`coding-standards-apply-deprecation.md`. Keep existing verifier scripts only
until their coverage exists in Pest, then remove them.
