# Phase 1.30 Plan - General Module Event / Observer System

## Goal

Give every module the freedom to **emit named events** and to **subscribe to any
event** (core's or another module's) via its own `config/events.php` - **without
editing core or each other.** This is the connective tissue that makes Zoosper's
ecosystem genuinely composable, and the concrete answer to *"what freedom will it
have?"*

## Plain-English primer

- An **event** = *"something happened"* (a page was published; a user logged in).
- A **listener / observer** = a small class that **reacts** to it.
- **Fire-and-forget:** the code that emits the event does not know or care who is
  listening. That is exactly what lets modules extend each other without coupling.

## CRITICAL design distinction (read this first)

Zoosper deliberately has **two** extensibility mechanisms, kept **separate** on
purpose:

| | **Entity-save LIFECYCLE** (1.20-1.28) | **General EVENTS** (1.30, this) |
|---|---|---|
| Shape | Ordered stages around a save | Fire-and-forget notifications |
| Can mutate the payload? | ✅ yes (`EntitySaveContext`) | Reads/enriches a payload object, but... |
| Can **abort** the action? | ✅ yes (`addError` -> runner stops before persist) | ❌ **no** - observers cannot stop what happened |
| Use it for | Validating/mutating a save **in progress** | **Side effects after the fact:** cache clear, audit log, notifications, search indexing |

Keeping them separate avoids a classic foot-gun: an observer must never be able to
accidentally break a save. Validation belongs in the lifecycle; reactions belong
in events.

## Design (mirrors the proven Phase 1.28 pattern)

- `Zoosper\Core\Event\EventDispatcherInterface`
  - `dispatch(string $eventName, object $event): object` - returns the (possibly
    enriched) event object. Observers may read/enrich it but cannot abort the
    originating action.
- `Zoosper\Core\Event\EventDispatcher` (concrete)
  - `listen(string $eventName, callable|EventListenerInterface $listener): self`
  - `dispatch(string $eventName, object $event): object`
  - `listeners(string $eventName): array`
  - Same shape as `EntitySaveEventDispatcher`, so it is instantly familiar.
- `Zoosper\Core\Event\EventListenerInterface`
  - `handle(object $event): void`
- Event payloads - support **both**:
  - **Typed event classes** for core events (clear contracts, e.g.
    `PagePublishedEvent` with a readonly `int $pageId`).
  - A generic `Zoosper\Core\Event\GenericEvent` (name + readonly context array)
    for quick module-defined events without a bespoke class.
- `Zoosper\Core\Event\ModuleEventListenerLoader` (mirror
  `ModuleEntitySaveListenerLoader`)
  - Reads each enabled module's `config/events.php` returning
    `[EventName => [Listener::class | callable, ...]]`.
  - Resolves each entry: instance -> as-is; callable -> as-is; class-string ->
    container-first, else `new`.
  - Throws a **descriptive `ZoosperException`** on misconfiguration.
- **DI:** register a shared `EventDispatcherInterface` in core `services.php`,
  built by the loader - exactly like the save dispatcher in admin `services.php`.

## Event naming convention

Dot-namespaced, module-prefixed for third parties:

- Core: `page.published`, `page.unpublished`, `admin_user.logged_in`.
- Third-party: `acme_blog.post.featured`.

Core event names are exposed as class constants (e.g.
`PageEvents::PUBLISHED = 'page.published'`) to avoid typos; modules may use raw
strings.

## Per-listener error policy (a deliberate decision)

Because observers must **never** break the action that emitted the event, the
dispatcher wraps each listener call in `try/catch (Throwable)` and:

1. logs the failure via `ErrorHandler::logException()` (redacted), and
2. continues to the next listener.

A broken observer degrades gracefully; it cannot take down a publish. This is the
opposite of the save lifecycle (where an error *should* stop the save) - and that
difference is the whole point of keeping them separate.

## First core events (start small, incremental)

- `page.published` / `page.unpublished` - emitted from `PageAdminController`
  publish/unpublish (we own that file, so the first drop can include a real
  emitter).
- *(Later)* `admin_user.logged_in` - emitted from the login flow (defer until we
  have that file).
- *(Later)* `entity.saved` - a thin bridge emitted after the lifecycle's
  `COMMIT_AFTER`, so observers can react to **any** save without touching the
  lifecycle.

## Scope IN (first drop)

`EventDispatcher` + `EventDispatcherInterface` + `EventListenerInterface` +
`GenericEvent` + `PagePublishedEvent`/`PageUnpublishedEvent` + `PageEvents`
constants + `ModuleEventListenerLoader` + DI registration + **co-located Pest
tests** (dispatch order, container/`new`/callable resolution, descriptive errors,
and the catch-and-log-continue policy) + docs + a **real emitter** in
`PageAdminController` + an **example** `config/events.php` listener.

## Scope OUT

- Not folding the entity-save lifecycle into this (separate by design).
- No method interception (that is a later phase).
- No async/queued events (future).

## Pros / cons vs doing the generator CLI (1.31) first

| | Events first (recommended) | Generator first |
|---|---|---|
| **Pros** | The connective tissue that makes modules composable; the honest answer to "what freedom"; generalises the proven 1.28 pattern | Great DX; makes "drop in a folder" a one-command start |
| **Cons** | Slightly less flashy than a generator | Higher DX, but **lower architectural leverage** - better once events exist so scaffolds can include `config/events.php` |

**Decision: events first, generator (1.31) right after** - so the scaffold can
include an `events.php` example.

## Risks & mitigations

| Risk | Mitigation |
|---|---|
| An observer throws and breaks the emitter | Dispatcher catches per-listener `Throwable`, logs via `ErrorHandler`, continues (documented policy) |
| Event-name typos | Class-constant names for core events (`PageEvents::*`) |
| Modules relying on observer order for correctness | Document: order = registration order and must **not** be relied upon |
| Confusion with the save lifecycle | The "two mechanisms" table in the docs + naming (`Event*` vs `EntitySave*`) |

## Acceptance criteria

- A module can drop `config/events.php` and react to `page.published` with **zero
  core edits**.
- A throwing observer is **caught + logged** and does **not** break publish.
- Pest suite green (adds ~5-6 tests).
- Docs written (`docs/architecture/events.md` + a "writing event listeners"
  contributor guide, mirroring the save-listeners guide).

## Dependencies

**None new.** I already have `PageAdminController` (to add the emit), core
`services.php` (to register the dispatcher), and `ErrorHandler` is resolvable
there (registered in `ApplicationFactory`). **This phase can start immediately -
no new dumps required.**

## Sequenced steps

1. **Tests first** - dispatcher + loader (mirror the 1.28 tests).
2. `EventDispatcher` / interface / `GenericEvent` / `ModuleEventListenerLoader`.
3. DI registration in core `services.php`.
4. Emit `page.published` / `page.unpublished` from `PageAdminController` +
   ship an example `config/events.php` listener.
5. Per-listener catch-and-log policy (+ a test proving a throwing listener does
   not break dispatch).
6. Docs (`events.md`, writing-event-listeners guide) + roadmap update.
7. *(Later)* more emitters + the `entity.saved` bridge after `COMMIT_AFTER`.
